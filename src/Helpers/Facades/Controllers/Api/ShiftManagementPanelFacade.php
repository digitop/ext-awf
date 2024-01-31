<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\WORKCENTER;
use App\Models\DASHBOARD;

class ShiftManagementPanelFacade extends Facade
{
    public function default(string $dashboardId)
    {
        $workCenter = WORKCENTER::where(
            'WCSHNA',
            '=',
            DASHBOARD::where('DHIDEN', '=', $dashboardId)->first()->operatorPanels[0]->WCSHNA
        )->first();

        $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->update([
            'WFVALU' => 'default',
        ]);

        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SEQUENCE_CHANGE/', [
            [
                "to" => 'dh:' . $dashboardId,
                "payload" => [
                    "status" => "default",
                ],
            ]
        ]);
    }
}
