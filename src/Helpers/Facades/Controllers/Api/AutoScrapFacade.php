<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class AutoScrapFacade extends Facade
{
    public function scrap(Model $workCenter): JsonResponse
    {
        $status = 'plc_failure';

        $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
            'WFVALU' => $status,
        ]);

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
