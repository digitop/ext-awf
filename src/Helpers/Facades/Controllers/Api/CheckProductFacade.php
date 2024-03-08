<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use App\Http\Controllers\api\dashboard\scrapStation\ScrapStationController;
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

        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';
        $database = config('database.connections.mysql.database');

        $waitings = DB::connection('custom_mysql')->select('
            select asl.LSTIME, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL, a.SEINPR, a.PRCODE,
                   a.ORCODE, r.PORANK, r.OPSHNA, p.PRNAME, r.RNREPN
            from AWF_SEQUENCE a
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = "' . $workCenter->WCSHNA . '"
                left join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = r.WCSHNA
                left join AWF_SEQUENCE_WORKCENTER asw on a.SEQUID = asw.SEQUID and asw.WCSHNA = r.WCSHNA
            where a.SEINPR < r.PORANK
                and (asl.LSTIME >= "' . $start . '" or asl.LSTIME is null)
            order by asl.LSTIME DESC, a.SEQUID limit 1
        ');

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

        if (empty($serial) || $serial->PRCODE !== $waitings[0]->PRCODE) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.check.not_next_product')
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

        if (is_array($serialCheck) && $serialCheck['success'] == false) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.check.not_next_product')
                ),
                Response::HTTP_OK
            ));
        }

        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SCAN_OK/', [
            [
                "to" => 'wc:' .  $workCenter->WCSHNA,
                "payload" => [
                    "status" => true,
                ],
            ]
        ]);

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                [
                    'orderCode' => AWF_SEQUENCE::where('SEQUID', '=', $waitings[0]->SEQUID)->first()->ORCODE,
                    'name' => $waitings[0]?->PRNAME,
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
