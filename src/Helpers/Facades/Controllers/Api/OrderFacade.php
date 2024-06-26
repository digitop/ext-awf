<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use App\Http\Controllers\api\dashboard\operatorPanel\OperatorPanelController;
use App\Http\Controllers\api\dashboard\scrapStation\ScrapStationController;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Responses\CustomJsonResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\SERIALNUMBER;
use App\Models\WORKCENTER;
use App\Models\DASHBOARD;
use App\Models\REPNO;

class OrderFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $success = false;
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

        if ($model === null || empty($model)) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    $success,
                    [],
                    'Nem található munkaállomás'
                ),
                Response::HTTP_OK
            ));
        }

        $database = config('database.connections.mysql.database');
        $sequence = null;

        $waitings = DB::connection('custom_mysql')->select('
            select a.PRCODE, a.ORCODE, p.PRNAME
            from AWF_SEQUENCE a
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = r.WCSHNA
            where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
            '" and a.SEINPR = r.PORANK)) and asl.LETIME is null
            order by a.SEQUID limit 1
        ');

        if (array_key_exists(0, $waitings) && !empty($waitings[0])) {
            $sequence = $waitings[0];
            $success = true;
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                $success,
                [
                    'orderCode' => $sequence?->ORCODE ?? null,
                    'name' => $sequence?->PRNAME ?? null,
                ],
                $success ? '' : 'Nem áll rendelkezésre szekvencia adat'
            ),
            Response::HTTP_OK
        ));
    }

    /**
     * API endpoint for barcode/serial verification of welder stations
     */
    public function store(FormRequest|Request $request, Model|string|null ...$model): JsonResponse|null
    {
        $success = false;
        $database = config('database.connections.mysql.database');
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

        if (!$request->has('serial')) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    $success,
                    [
                        'orderCode' => null,
                        'side' => null,
                        'name' => null,
                    ],
                    $success ? '' : 'Nem érkezett serial number'
                ),
                Response::HTTP_OK
            ));
        }

        if (empty($request->serial)) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    $success,
                    [
                        'orderCode' => null,
                        'side' => null,
                        'name' => null,
                    ],
                    $success ? '' : 'Nem érkezett serial number'
                ),
                Response::HTTP_OK
            ));
        }

        $workCenter = WORKCENTER::where('WCSHNA', '=', $model[0]->operatorPanels[0]->WCSHNA)->first();

        $queryString = '
            select asl.LSTIME, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL, a.SEINPR, a.PRCODE,
                   a.ORCODE, r.PORANK, r.OPSHNA, p.PRNAME, r.RNREPN
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
            where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
            '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
            ' order by a.SEQUID limit 1
        ';

        $waitings = DB::connection('custom_mysql')->select($queryString);

        if (!array_key_exists(0, $waitings) || empty($waitings[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.no_new_data_available')
                ),
                Response::HTTP_OK
            ));
        }

        $serial = DB::select('
            select s.SNRDCN, r.RNREPN, s.PRCODE from SERIALNUMBER s
                join REPNO r on r.ORCODE = substring(s.RNREPN, 1, position("-" in s.RNREPN) - 1) and
                     s.SNSERN = "' . $request->serial . '" and r.WCSHNA = "' . $workCenter->WCSHNA . '"'
        );

        if (!array_key_exists(0, $serial) || empty($serial[0]) || $serial[0]->PRCODE !== $waitings[0]->PRCODE) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.check.not_next_product')
                ),
                Response::HTTP_OK
            )
            );
        }

        $serial = $serial[0];

        $serialCheck = (new OperatorPanelController())->checkAndSaveSerial(
            new Request([
                'SNSERN' => $request->serial,
                'RNREPN' => $serial->RNREPN,
                'SNCOUN' => 1,
                'SNRDCN' => $serial->SNRDCN,
                'subProduct' => false,
                'parentSNSERN' => false,
                'PRCODE' => $waitings[0]->PRCODE,
            ]),
            $model[0]->DHIDEN
        );

        if (
            is_array($serialCheck) &&
            (
                $serialCheck['success'] == false ||
                (
                    array_key_exists('serials', $serialCheck) &&
                    $serialCheck['serials'][0]['isNew'] == false &&
                    $serialCheck['serials'][0]['isReproduced'] == false
                )
            )
        ) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    is_array($serialCheck) || is_array($serialCheck) ? json_encode($serialCheck) :
                        __('response.check.not_next_product')
                ),
                Response::HTTP_OK
            )
            );
        }

        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/AWF_ORDER_CHECK_SERIAL/', [
            [
                "to" => 'wc:' . $workCenter->WCSHNA,
                "payload" => [
                    "success" => true,
                    'serial' => $request->serial,
                ],
            ]
        ]);

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                [
                    'orderCode' => $waitings[0]->ORCODE ?? null,
                    'side' => $waitings[0]->SESIDE ?? null,
                    'name' => $waitings[0]->PRNAME ?? null,
                ]
            ),
            Response::HTTP_OK
        ));
    }
}
