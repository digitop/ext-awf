<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement;

use App\Http\Controllers\api\dashboard\operatorPanel\OperatorPanelController;
use AWF\Extension\Helpers\Facades\Controllers\Api\CheckProductFacade;
use AWF\Extension\Helpers\Facades\Controllers\Web\Facade;
use AWF\Extension\Requests\Api\CheckProductCheckRequest;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View as IlluminateView;
use Illuminate\Support\Facades\DB;
use App\Models\WORKCENTER;

class ShiftManagementPanelManualDataRecordFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null,
        Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        return view(
            'awf-extension::display.shift_management_panel.partials.manual_data_record',
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
        $sequence = DB::connection('custom_mysql')->select('
            select asw.WCSHNA, asw.RNREPN, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEPSEQ, a.PRCODE from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID
                join AWF_SEQUENCE_WORKCENTER asw on a.SEQUID = asw.SEQUID
                where asw.WCSHNA = "' . $model[0]->WCSHNA . '" and asl.LSTIME is null and asl.LETIME is null
        ');

        if (!empty($sequence[0])) {
            $sequence = $sequence[0];
        }

        return view(
            'awf-extension::display.shift_management_panel.partials.manual_data_record_partials.workCenter',
            [
                'sequence' => $sequence
            ]
        );
    }

    public function update(
        Request|FormRequest $request, Model|string ...$model
    ): Application|Factory|View|ContractsApplication|null
    {
        $workCenter = $model[0];

        $check = (new CheckProductFacade())->check(
            new CheckProductCheckRequest([
                'serial' => $request->serial,
                'dashboard' => $workCenter->operatorPanels[0]->dashboard->DHIDEN,
            ])
        );

        $sequence = DB::connection('custom_mysql')->select('
            select asw.WCSHNA, a.SEQUID, asw.RNREPN, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEPSEQ, a.PRCODE from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID
                join AWF_SEQUENCE_WORKCENTER asw on a.SEQUID = asw.SEQUID
                where asw.WCSHNA = "' . $model[0]->WCSHNA . '" and asl.LSTIME is null and asl.LETIME is null
        ');

        if (!empty($sequence[0])) {
            $sequence = $sequence[0];
        }

//        if ($check->getData()?->success == false) {
//            return view(
//                'awf-extension::display.shift_management_panel.partials.manual_data_record_partials.workCenter',
//                [
//                    'sequence' => $sequence,
//                    'error' => $check->getData()->message
//                ]
//            );
//        }

        $checkSerial = (new OperatorPanelController())->checkAndSaveSerial(
            new Request([
                'SNSERN' => $sequence[0]->SEQUID,
                'RNREPN',
                'subProduct' => false,
                'PRCODE',
            ]),
            $workCenter->operatorPanels[0]->dashboard->DHIDEN
        );

        return null;
    }
}
