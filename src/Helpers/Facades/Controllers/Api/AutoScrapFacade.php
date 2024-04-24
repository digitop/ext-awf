<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AutoScrapFacade extends Facade
{
    public function scrap(Model $workCenter): JsonResponse
    {
        $status = 'plc_failure';
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';
        $database = config('database.connections.mysql.database');

        $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
            'WFVALU' => $status,
        ]);

        $queryString = '
            select a.PRCODE, a.ORCODE, r.RNREPN, r.WCSHNA
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
            where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
            '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
            ' order by a.SEQUID limit 1'
        ;

        $sequence = DB::connection('custom_mysql')->select($queryString);

        if (!array_key_exists(0, $sequence) || empty($sequence[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false
                ),
                Response::HTTP_OK
            ));
        }

        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SEQUENCE_SCRAP/', [
            [
                "to" => 'dh:' . $workCenter->operatorPanels[0]->dashboard->DHIDEN,
                "payload" => [
                    "status" => $status
                ],
            ]
        ]);

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true
            ),
            Response::HTTP_OK
        ));
    }
}
