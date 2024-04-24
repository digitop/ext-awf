<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement;

use AWF\Extension\Helpers\Facades\Controllers\Web\Facade;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View as IlluminateView;

class ShiftManagementDefaultPanelFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null,
                           Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        return view('awf-extension::display.shift_management_panel.partials.default');
    }

    public function set(): JsonResponse
    {
        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/web/SHELF_RESET/', [
            [
                "to" => 'wc:' . 'EL01',
                "payload" => [
                    "status" => "default",
                ],
            ]
        ]);

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true
            ),
            Response::HTTP_OK
        )
        );
    }
}
