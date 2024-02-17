<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\WORKCENTER;
use App\Models\DASHBOARD;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ShiftManagementPanelFacade extends Facade
{
    public function default(string $dashboardId): JsonResponse
    {
        $database = config('database.connections.mysql.database');

        $workCenter = WORKCENTER::where(
            'WCSHNA',
            '=',
            DASHBOARD::where('DHIDEN', '=', $dashboardId)->first()->operatorPanels[0]->WCSHNA
        )->first();

        $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->update([
            'WFVALU' => 'default',
        ]);

        $queryString = '
            select a.ORCODE from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
            where asl.LSTIME is null and asl.LETIME is null and a.SEINPR = (ppd.PORANK - 1) and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '" order by a.SEQUID limit 1'
        ;

        $sequence = DB::connection('custom_mysql')->select($queryString);

        if (array_key_exists(0, $sequence)) {
            $sequence = $sequence[0];
        }

        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SEQUENCE_CHANGE/', [
            [
                "to" => 'dh:' . (int)$dashboardId,
                "payload" => [
                    "status" => "default",
                    'orderCode' => is_object($sequence) ? $sequence?->ORCODE : null,
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
