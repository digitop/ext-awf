<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use App\Models\DASHBOARD_MODULE_SETTINGS;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Events\Dashboard\ProductQualified;
use App\Models\WORKCENTER;
use App\Models\REPNO;
use Illuminate\Support\Facades\DB;

class ScrapFacade extends Facade
{
    public function index(ProductQualified $event): JsonResponse
    {
        if ($event->scrapReport !== false) {
            $moduleSetting = DASHBOARD_MODULE_SETTINGS::where([
                ['DHIDEN', $event->DHIDEN],
                ['DMSKEY', 'scrapStationFilter']
            ])
                ->first();

            if ($moduleSetting) {
                $scrapStationFilter = $moduleSetting->DMSVAL;
                // Ha van beallitva ertek a szuroben
                $workCenter = WORKCENTER::find($scrapStationFilter); // Selejt állomás megkeresese
            }

            if (empty($workCenter)) {
                return new CustomJsonResponse(new JsonResponseModel(
                    new ResponseData(
                        false,

                    ),
                    Response::HTTP_OK
                ));
            }

            $sequence = DB::connection('custom_mysql')->select('
                select a.* from AWF_SEQUENCE a
                    join AWF_SEQUENCE_WORKCENTER asw on a.SEQUID = asw.SEQUID and asw.WCSHNA = "' . $workCenter->WCSHNA . '"
                    join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asw.WCSHNA = asl.WCSHNA
                where asl.LSTIME is not null and asl.LETIME is null and a.SEINPR > 0
            ');

            if (array_key_exists(0, $sequence) && !empty($sequence[0])) {
                $sequence = $sequence[0];
            }

            if (empty($sequence)) {
                return new CustomJsonResponse(new JsonResponseModel(
                    new ResponseData(
                        false,

                    ),
                    Response::HTTP_OK
                ));
            }

            AWF_SEQUENCE::where('SEQUID', '=', $sequence->SEQUID)
                ->where('SEINPR', '=', $sequence->SEINPR)
                ->first()
                ?->update([
                    'SEINPR' => 0,
                    'SESCRA' => true,
                ]);

            $logs = AWF_SEQUENCE_LOG::where('SEQUID', '=', $sequence->SEQUID)
                ->whereNotNull('LSTIME')
                ->get();
            $now = (new \DateTime())->format('Y-m-d H:i:s');

            foreach ($logs as $log) {
                $log->update(['LETIME' => $now]);
            }

            AWF_SEQUENCE_LOG::create([
                'SEQUID' => $sequence->SEQUID,
                'WCSHNA' => 'EL01',
            ]);

            $sequence = AWF_SEQUENCE::where('SEQUID', $sequence->SEQUID)
                ->where('SEINPR', '=', 0)
                ->where('SESCRA', true)
                ->first();

            AWF_SEQUENCE_WORKCENTER::create([
                'SEQUID' => $sequence->SEQUID,
                'WCSHNA' => 'EL01',
                'RNREPN' => REPNO::where('ORCODE', '=', $sequence->ORCODE)->where('WCSHNA', '=', 'EL01')->first()->RNREPN
            ]);

            publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SEQUENCE_SCRAP/', [
                [
                    "to" => 'wc:' . $workCenter->WCSHNA,
                    "payload" => [
                        "status" => true,
                        'state' => 'scrap',
                    ],
                ]
            ]);

            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    true,
                ),
                Response::HTTP_OK
            ));
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                false,
                [],
                __('response.unprocessable_entity')
            ),
            Response::HTTP_OK
        ));
    }
}
