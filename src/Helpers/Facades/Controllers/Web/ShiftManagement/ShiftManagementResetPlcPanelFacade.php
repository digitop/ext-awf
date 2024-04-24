<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement;

use App\Models\WORKCENTER;
use AWF\Extension\Helpers\Facades\Controllers\Web\Facade;
use AWF\Extension\Helpers\Models\ShiftManagementResettingEventModel;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View as IlluminateView;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\REPNO;
use App\Models\PRODUCT;
use App\Models\CDPVAR;

class ShiftManagementResetPlcPanelFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null,
        Model|string|null $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        return view(
            'awf-extension::display.shift_management_panel.partials.reset_plc',
            [
                'workCenters' => WORKCENTER::whereNotIn(
                    'WCSHNA',
                    ["EL01", "PSAPB01", "PSAPJ01", "PSZAPB01", "PSZAPJ01"]
                )
                    ->orderBy('WCSHNA')
                    ->get()
            ]
        );
    }

    public function show(
        Request|FormRequest|null $request = null,
        Model|string|null ...$model
    ): Application|Factory|View|ContractsApplication|null
    {
        $workCenter = $model[0];
        $data = [];
        $data['WCSHNA'] = $workCenter->WCSHNA;

        $data['data'] = CDPVAR::where('WCSHNA', '=', $workCenter->WCSHNA)
            ->whereNotNull('CVDESC')
            ->get();

        return view(
            'awf-extension::display.shift_management_panel.partials.reset_plc_partials.work_center',
            [
                'data' => $data,
                'workCenter' => $workCenter,
            ]
        );
    }

    public function reset(Request|FormRequest $request, Model $model): JsonResponse
    {
        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/web/shift-management-reset-default/', [
            [
                "to" => 'wc:' . $model->WCSHNA,
                "payload" => [
                    'success' => true,
                    'status' => ShiftManagementResettingEventModel::DEFAULT,
                    'color' => $request->cdpvars
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
