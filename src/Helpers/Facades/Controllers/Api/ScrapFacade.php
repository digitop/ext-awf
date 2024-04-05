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
        $database = config('database.connections.mysql.database');
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

        if ($event->scrapReport !== false) {
            $operatorPanel = OPERATOR_PANEL::where('DHIDEN', '=', $event->DHIDEN)->first();

            if ($operatorPanel) {
                $scrapStationFilter = $operatorPanel->DMSVAL;
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

            $queryString = '
                select a.PRCODE, a.ORCODE,r.OPSHNA, r.RNREPN, a.SEQUID, a.SEPSEQ, a.SEARNU, a.SESIDE, a.SEPILL, a.SEPONR,
                       a.SEINPR, p.PRNAME, p.PRCODE
                from AWF_SEQUENCE_LOG asl
                    join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                    join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE
                    join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
                '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                    asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
                ' order by a.SEQUID limit 1';

            $sequenceLog = DB::connection('custom_mysql')->select($queryString);

            if (array_key_exists(0, $sequenceLog) && !empty($sequenceLog[0])) {
                $sequenceLog = $sequenceLog[0];
            }

            if (empty($sequenceLog)) {
                return new CustomJsonResponse(new JsonResponseModel(
                    new ResponseData(
                        false,

                    ),
                    Response::HTTP_OK
                ));
            }

            AWF_SEQUENCE::where('SEQUID', '=', $sequenceLog->SEQUID)
                ->where('SEINPR', '=', $sequenceLog->SEINPR)
                ->first()
                ?->update([
                    'SEINPR' => 0,
                    'SESCRA' => true,
                ]);

            $logs = AWF_SEQUENCE_LOG::where('SEQUID', '=', $sequenceLog->SEQUID)
                ->whereNotNull('LSTIME')
                ->get();
            $now = (new \DateTime())->format('Y-m-d H:i:s');

            foreach ($logs as $log) {
                $log->update(['LETIME' => $now]);
            }

            AWF_SEQUENCE_LOG::create([
                'SEQUID' => $sequenceLog->SEQUID,
                'WCSHNA' => 'EL01',
            ]);

            $sequence = AWF_SEQUENCE::where('SEQUID', $sequenceLog->SEQUID)
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
