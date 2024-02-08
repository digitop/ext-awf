<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement;

use App\Http\Controllers\api\dashboard\operatorPanel\OperatorPanelController;
use App\Models\REPNO;
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
        Model|string|null $model = null
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
        $database = config('database.connections.mysql.database');

        $sequence = DB::connection('custom_mysql')->select('
            select asw.WCSHNA, asw.RNREPN, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEPSEQ, a.PRCODE, s.SNSERN from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID
                join AWF_SEQUENCE_WORKCENTER asw on a.SEQUID = asw.SEQUID
                join ' . $database . '.SERIALNUMBER s on s.RNREPN = asw.RNREPN
                where asw.WCSHNA = "' . $model[0]->WCSHNA . '" and asl.LSTIME is null
                 and asl.LETIME is null and a.SEINPR = 1
        ');

        if (!empty($sequence[0])) {
            $sequence = $sequence[0];
        }

        return view(
            'awf-extension::display.shift_management_panel.partials.manual_data_record_partials.work_center',
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
        $database = config('database.connections.mysql.database');

        $sequence = DB::connection('custom_mysql')->select('
            select asw.WCSHNA, asw.RNREPN, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEPSEQ, a.PRCODE, s.SNSERN from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID
                join AWF_SEQUENCE_WORKCENTER asw on a.SEQUID = asw.SEQUID
                join ' . $database . '.SERIALNUMBER s on s.RNREPN = asw.RNREPN
                where asw.WCSHNA = "' . $model[0]->WCSHNA . '" and asl.LSTIME is null
                 and asl.LETIME is null and a.SEINPR = 1
        ');

        if (!empty($sequence[0])) {
            $sequence = $sequence[0];
        }

        $checkSerial = (new OperatorPanelController())->checkAndSaveSerial(
            new Request([
                'SNSERN' => $request->serialNumber,
                'RNREPN' => $sequence->RNREPN,
                'SNCOUN' => 1,
                'SNRDCN' => 1,
                'subProduct' => false,
                'parentSNSERN' => false,
                'PRCODE' => $sequence->PRCODE,
            ]),
            $workCenter->operatorPanels[0]->dashboard->DHIDEN
        );

        $error = '';

        if ($checkSerial['success'] == false) {
            if (array_key_exists('error', $checkSerial) && !empty($checkSerial['error'])) {
                $error = $checkSerial['error'];
            }
            else {
                $error = 'Hiba lépett fel az adatok mentése során!';
            }
        }
        else {
            $sequence->serial = $request->serialNumber;
        }

        return view(
            'awf-extension::display.shift_management_panel.partials.manual_data_record_partials.work_center',
            [
                'sequence' => $sequence,
                'error' => $checkSerial['success'] == false && !empty($error) ? $error : ''
            ]
        );
    }
}
