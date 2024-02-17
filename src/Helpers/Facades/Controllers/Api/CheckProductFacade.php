<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
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

        $isWelder = in_array($workCenter->WCSHNA, ['HA01', 'HB01', 'HC01'], true);

        $waitings = DB::connection('custom_mysql')->select('
            select asl.LSTIME, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL, a.SEINPR, a.PRCODE, a.ORCODE, ppd.PFIDEN, ppd.PORANK, ppd.OPSHNA
            from AWF_SEQUENCE a
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = "' . $workCenter->WCSHNA . '"
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                left join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = pcd.WCSHNA
                left join AWF_SEQUENCE_WORKCENTER asw on a.SEQUID = asw.SEQUID and asw.WCSHNA = pcd.WCSHNA
            where a.SEINPR < ppd.PORANK
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

        if (empty($serial) || (int)$serial->SNCYID < 0) {
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
