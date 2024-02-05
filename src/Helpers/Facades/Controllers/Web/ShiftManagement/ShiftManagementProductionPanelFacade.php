<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement;

use App\Models\WORKCENTER;
use AWF\Extension\Helpers\Facades\Controllers\Web\Facade;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View as IlluminateView;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\REPNO;
use App\Models\PRODUCT;

class ShiftManagementProductionPanelFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null,
                           Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        return view('awf-extension::display.shift_management_panel.partials.production', ['data' => []]);
    }

    public function get(): JsonResponse
    {
        $data = [];
        $workCenters = WORKCENTER::whereNotIn('WCSHNA', ["EL01", "PSAPB01", "PSAPJ01", "PSZAPB01", "PSZAPJ01"])
            ->orderBy('WCSHNA')
            ->get();

        $database = config('database.connections.mysql.database');

        $sequences = DB::connection('custom_mysql')->select('
            select asw.WCSHNA, a.PRCODE, a.SEPILL, a.SESIDE, asl.LSTIME, asw.RNREPN, a.ORCODE, a.SEPONR, a.SEPSEQ, pf.FEVALU as color, pf2.FEVALU as material
                from AWF_SEQUENCE a
                join AWF_SEQUENCE_WORKCENTER asw on asw.SEQUID = a.SEQUID and asw.WCSHNA not in ("EL01", "PSAPB01", "PSAPJ01", "PSZAPB01", "PSZAPJ01")
                left join AWF_SEQUENCE_LOG asl on asl.SEQUID = a.SEQUID and asl.WCSHNA = asw.WCSHNA
                join ' . $database . '.PRODUCTFEATURE pf on pf.PRCODE = a.PRCODE and pf.FESHNA = "SZASZ"
                join ' . $database . '.PRODUCTFEATURE pf2 on pf2.PRCODE = a.PRCODE and pf2.FESHNA = "SZAA"
                where asl.LSTIME is null and asl.LETIME is null
            order by asw.WCSHNA
        ');

        foreach ($workCenters as $workCenter) {
            $data['data'][$workCenter->WCSHNA] = [];
            $monthly = DB::connection('custom_mysql')->select('
            select count(a.SEQUID) as monthly from AWF_SEQUENCE a
             join ' . $database . '.ORDERHEAD o on o.ORCODE = a.ORCODE
             join ' . $database . '.REPNO r on o.ORCODE = r.ORCODE
             where SEEXPI between now() and (now() + interval 30 day) and r.WCSHNA =
            "' . $workCenter->WCSHNA . '"');

            $monthlyInProd = DB::connection('custom_mysql')->select('
            select count(a.SEQUID) as monthlyInProd from AWF_SEQUENCE a
             join ' . $database . '.ORDERHEAD o on o.ORCODE = a.ORCODE
             join ' . $database . '.REPNO r on o.ORCODE = r.ORCODE
             where SEEXPI between now() and (now() + interval 30 day) and r.WCSHNA = "' .
                $workCenter->WCSHNA . '" and a.SEINPR = 1
            ');

            $data['data'][$workCenter->WCSHNA]['monthly'] = $monthly[0]->monthly;
            $data['data'][$workCenter->WCSHNA]['monthlyInProd'] = $monthlyInProd[0]->monthlyInProd;
        }

        foreach ($sequences as $sequence) {
            $data['data'][$sequence->WCSHNA]['porscheProduct'] = $sequence->SEPONR;
            $data['data'][$sequence->WCSHNA]['porscheSequence'] = $sequence->SEPSEQ;
            $data['data'][$sequence->WCSHNA]['side'] = $sequence->SESIDE;
            $data['data'][$sequence->WCSHNA]['pillar'] = $sequence->SEPILL;
            $data['data'][$sequence->WCSHNA]['product'] = $sequence->PRCODE;
            $data['data'][$sequence->WCSHNA]['productColor'] = $sequence->color;
            $data['data'][$sequence->WCSHNA]['productMaterial'] = ucfirst($sequence->material);
        }

        $data['timeout'] = 10000;

        return new JsonResponse($data);
    }
}
