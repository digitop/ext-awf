<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement;

use App\Http\Controllers\api\dashboard\operatorPanel\OperatorPanelController;
use AWF\Extension\Helpers\DataTable\ManualDataRecordDataTable;
use AWF\Extension\Helpers\Facades\Controllers\Api\SequenceFacade;
use AWF\Extension\Helpers\Facades\Controllers\Web\Facade;
use AWF\Extension\Requests\Api\SequenceShowRequest;
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
use App\Models\SERIALNUMBER;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class ShiftManagementPanelManualDataRecordFacade extends Facade
{
    public function index(
        ManualDataRecordDataTable $dataTable,
        string|null $workCenterId
    ): Application|Factory|View|IlluminateView|ContractsApplication|JsonResponse|null
    {
        if (empty(Session::get('locale'))) {
            Session::put('locale', 'hu_HU');
        }

        $dataTable->setWorkCenterId($workCenterId);

        return $dataTable->render(
            'awf-extension::display/shift_management_panel.partials.manual_data_record_partials.table',
            [
                'workCenterId' => $workCenterId
            ]
        );
    }

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
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

        $queryString = '
            select a.SEQUID, r.WCSHNA, r.RNREPN, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEPSEQ, a.PRCODE, s.SNSERN
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
                left join ' . $database . '.SERIALNUMBER s on s.RNREPN = r.RNREPN ' .
            'where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
            '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                asl.WCSHNA = "' . $model[0]->WCSHNA . '"' .
            ' order by a.SEQUID limit 1';

        $sequence = DB::connection('custom_mysql')->select($queryString);

        if (!empty($sequence[0])) {
            $sequence = $sequence[0];
        }

        return view(
            'awf-extension::display.shift_management_panel.partials.manual_data_record_partials.work_center',
            [
                'sequence' => $sequence,
                'workCenter' => $model[0]
            ]
        );
    }

    public function update(
        Request|FormRequest $request, Model|string ...$model
    ): Application|Factory|View|ContractsApplication|RedirectResponse|null
    {
        $workCenter = $model[0];
        $database = config('database.connections.mysql.database');
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

        $queryString = '
            select a.SEQUID, r.WCSHNA, r.RNREPN, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEPSEQ, a.PRCODE, s.SNSERN,
                   r.PORANK, a.SEINPR
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
                left join ' . $database . '.SERIALNUMBER s on s.RNREPN = r.RNREPN ' .
            'where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
            '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                asl.WCSHNA = "' . $model[0]->WCSHNA . '"' .
            ' order by a.SEQUID limit 1';

        $sequence = DB::connection('custom_mysql')->select($queryString);

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
                $error = __(
                    'response.check.cannot_attach_piece',
                    ['waiting' => $sequence->PRCODE, 'got' => $request->serialNumber]
                );
            }
        }
        else {
            $sequence->serial = $request->serialNumber;

            SERIALNUMBER::where('SNSERN', '=', $request->serialNumber)
                ->where('RNREPN', '=', $sequence->RNREPN)
                ->update(['SNCYID' => ($model[0]->WCTYPE == 'QS' ? -2 : 10)]);

            $nextProductDetails = DB::select('
            select PORANK as station from REPNO r where r.RNACTV = 1 and r.ORCODE = "' . $sequence->ORCODE .
                '" and r.WCSHNA = "' . $model[0]->WCSHNA . '"'
            );

            if (array_key_exists(0, $nextProductDetails) && !empty($nextProductDetails[0])) {
                $nextProductDetails = $nextProductDetails[0]->station;

                if ($sequence->SEINPR < $nextProductDetails) {
                    (new SequenceFacade())->show(
                        new SequenceShowRequest([
                            $sequence->SESIDE,
                            1,
                            'false',
                            'false',
                            $request->serialNumber,
                        ]),
                        $model[0],
                        $sequence->SEPILL
                    );
                }
            }
        }

        return redirect()
            ->route('awf-shift-management-panel.manual-data-record.show', ['WCSHNA' => $workCenter->WCSHNA])
            ->with('error', $error);

        return view(
            'awf-extension::display.shift_management_panel.partials.manual_data_record_partials.work_center',
            [
                'sequence' => $sequence,
                'error' => $checkSerial['success'] == false && !empty($error) ? $error : ''
            ]
        );
    }
}
