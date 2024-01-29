<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement;

use AWF\Extension\Helpers\DataTable\ShiftStartDataTable;
use AWF\Extension\Helpers\Facades\Controllers\Web\Facade;
use AWF\Extension\Models\AWF_SEQUENCE;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View as IlluminateView;

class ShiftManagementShiftStartPanelFacade extends Facade
{
    public function index(
        ShiftStartDataTable $dataTable
    ): Application|Factory|View|IlluminateView|ContractsApplication|JsonResponse|null
    {
        return $dataTable->render('awf-extension::display/shift_management_panel.partials.shift_start');
    }

    public function create(Request|FormRequest|null $request = null,
                           Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        $collection = AWF_SEQUENCE::where('SEINPR', '=', 0)
            ->orderBy('SEQUID', 'ASC')
            ->get();

        $data = [];

        foreach ($collection as $item) {
            if (!array_key_exists($item->SEPILL, $data)) {
                $data[$item->SEPILL] = $item;
            }
        }

        return view('awf-extension::display/shift_management_panel.partials.shift_start', [
            'aPillar' => $data['A'],
            'bPillar' => $data['B'],
            'cPillar' => $data['C'],
        ]);
    }
}
