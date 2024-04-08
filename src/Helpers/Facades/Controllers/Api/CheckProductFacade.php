<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use App\Http\Controllers\api\dashboard\scrapStation\ScrapStationController;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Models\SERIALNUMBER;
use App\Models\WORKCENTER;
use App\Models\DASHBOARD_MODULE_SETTINGS;
use App\Models\REPNO;
use App\Models\DASHBOARD;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Events\Api\OperatorPanelSaveSerialEvent;


/**
 * API endpoint for barcode/serial verification of qualification stations
 */
class CheckProductFacade extends Facade
{
    public function check(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $moduleSetting = DASHBOARD_MODULE_SETTINGS::where([
            ['DHIDEN', $request->dashboard],
            ['DMSKEY', 'scrapStationFilter']
        ])
            ->first();

        if ($moduleSetting) {
            $scrapStationFilter = $moduleSetting->DMSVAL;
            // Ha van beallitva ertek a szuroben
            $workCenter = WORKCENTER::find($scrapStationFilter); // Selejt állomás megkeresese
        }

        if (strtolower($request->serial) === 'dummy') {
            publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SCAN_OK/', [
                [
                    "to" => 'wc:' . $workCenter->WCSHNA,
                    "payload" => [
                        "status" => true,
                        "serial" => $request->serial,
                    ],
                ]
            ]);

            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    true,
                    [
                        'orderCode' => null,
                        'name' => null,
                        "serial" => $request->serial,
                    ]
                ),
                Response::HTTP_OK
            )
            );
        }

        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';
        $database = config('database.connections.mysql.database');

        $waitings = DB::connection('custom_mysql')->select('
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
            '
        );

        if (!array_key_exists(0, $waitings) || empty($waitings[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.check.not_next_product')
                ),
                Response::HTTP_OK
            ));
        }

        $serial = SERIALNUMBER::where('SNSERN', '=', $request->serial)
            ->first();

        if (empty($serial) || (array_key_exists(0, $waitings) && $serial->PRCODE !== $waitings[0]->PRCODE)) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __(
                        'response.check.cannot_attach_piece',
                        ['waiting' => $waitings[0]->PRCODE, 'got' => $serial?->PRCODE]
                    )
                ),
                Response::HTTP_OK
            ));
        }

        $serialCheck =(new ScrapStationController())->findSerial(
            $request->dashboard,
            new Request([
                'serial' => $request->serial,
            ]),
            $workCenter
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
                    json_encode($serialCheck)
                ),
                Response::HTTP_OK
            ));
        }

        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SCAN_OK/', [
            [
                "to" => 'wc:' .  $workCenter->WCSHNA,
                "payload" => [
                    "status" => true,
                    "serial" => $request->serial,
                ],
            ]
        ]);

        $sequence = AWF_SEQUENCE::where('SEQUID', '=', $waitings[0]->SEQUID)->first();
        $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $waitings[0]->SEQUID)->first();
        $orderCode = $sequence->ORCODE;

        if ($serial->RNREPN !== $sequenceWorkCenter->RNREPN) {
            //$orderCode = REPNO::where('RNREPN', '=', $serial->RNREPN)->with('orderhead')->first()->orderhead->PRCODE;
            $orderCode = REPNO::where('RNREPN', '=', $serial->RNREPN)->first()->ORCODE;
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                [
                    'orderCode' => $orderCode,
                    'name' => $waitings[0]?->PRNAME ?? null,
                    "serial" => $request->serial,
                ]
            ),
            Response::HTTP_OK
        ));
    }

    public function publis(OperatorPanelSaveSerialEvent $event)
    {
        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SCAN_OK/', [
            [
                "to" => 'wc:' .  $event->WCSHNA,
                "payload" => [
                    "status" => true,
                ],
            ]
        ]);
    }
}
