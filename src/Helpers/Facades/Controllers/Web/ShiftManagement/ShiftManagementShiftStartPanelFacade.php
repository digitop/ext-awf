<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement;

use AWF\Extension\Events\ShiftManagementResettingEvent;
use AWF\Extension\Helpers\DataTable\ShiftStartDataTable;
use AWF\Extension\Helpers\Facades\Controllers\Web\Facade;
use AWF\Extension\Helpers\Models\ShiftManagementResettingEventModel;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Responses\ShiftManagementResettingEventResponse;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View as IlluminateView;
use Illuminate\Support\Facades\Session;
use PhpMqtt\Client\Facades\MQTT;

class ShiftManagementShiftStartPanelFacade extends Facade
{
    public function index(
        ShiftStartDataTable $dataTable,
        string|null $pillar
    ): Application|Factory|View|IlluminateView|ContractsApplication|JsonResponse|null
    {
        if (empty(Session::get('locale'))) {
            Session::put('locale', 'hu_HU');
        }

        $dataTable->setPillar($pillar);
        return $dataTable->render('awf-extension::display/shift_management_panel.partials.table');
    }

    public function create(Request|FormRequest|null $request = null,
                           Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        $collection = AWF_SEQUENCE::whereIn('SEINPR', [0, 1])
            ->orderBy('SEQUID', 'ASC')
            ->get();

        $data = [];

        foreach ($collection as $item) {
            if (!array_key_exists($item->SEPILL, $data)) {
                $data[$item->SEPILL] = $item;
            }
        }

        if (!array_key_exists('A', $data)) {
            $data['A'] = [];
        }
        if (!array_key_exists('B', $data)) {
            $data['B'] = [];
        }
        if (!array_key_exists('C', $data)) {
            $data['C'] = [];
        }

        return view('awf-extension::display/shift_management_panel.partials.shift_start', [
            'aPillar' => $data['A'],
            'bPillar' => $data['B'],
            'cPillar' => $data['C'],
        ]);
    }

    public function default(): void
    {
        event(new ShiftManagementResettingEvent(
            (new ShiftManagementResettingEventResponse())->generate()
        ));

        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/web/shift-management-reset-default/', [
            [
                "to" => 'wc:' . 'EL01',
                "payload" => [
                    'success' => true,
                    'status' => ShiftManagementResettingEventModel::DEFAULT
                ],
            ]
        ]);
    }
}
