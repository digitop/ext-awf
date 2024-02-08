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
        return view(
            'awf-extension::display.shift_management_panel.partials.production',
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
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';
        $database = config('database.connections.mysql.database');
        $data = [];
        $data['WCSHNA'] = $workCenter->WCSHNA;

        $gotOver = DB::connection('custom_mysql')->select('
            select asl.LSTIME, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL, a.SEINPR, a.PRCODE, a.ORCODE, ppd.PFIDEN, ppd.PORANK, ppd.OPSHNA from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = "' . $workCenter->WCSHNA . '"
                join OEEM_AWF.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join OEEM_AWF.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                join OEEM_AWF.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
            where a.SEINPR > ppd.PORANK
                and (asl.LSTIME >= "' . $start . '" or asl.LSTIME is null)
            order by asl.LSTIME DESC, a.SEQUID
        ');

        if (!empty($gotOver[0])) {
            $data['gotOver'] = $gotOver[0];
        }

        $inPlace = DB::connection('custom_mysql')->select('
            select asl.LSTIME, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL, a.SEINPR, a.PRCODE, a.ORCODE, ppd.PFIDEN, ppd.PORANK, ppd.OPSHNA from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = "' . $workCenter->WCSHNA . '"
                join OEEM_AWF.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join OEEM_AWF.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                join OEEM_AWF.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
            where a.SEINPR = ppd.PORANK
                and (asl.LSTIME >= "' . $start . '" or asl.LSTIME is null)
            order by asl.LSTIME DESC, a.SEQUID
        ');

        if (!empty($inPlace[0])) {
            $data['inPlace'] = $inPlace[0];
        }

        $waitings = DB::connection('custom_mysql')->select('
            select asl.LSTIME, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL, a.SEINPR, a.PRCODE, a.ORCODE, ppd.PFIDEN, ppd.PORANK, ppd.OPSHNA from AWF_SEQUENCE a
                join OEEM_AWF.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join OEEM_AWF.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = "' . $workCenter->WCSHNA . '"
                join OEEM_AWF.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                left join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = pcd.WCSHNA
            where a.SEINPR < ppd.PORANK
                and (asl.LSTIME >= "' . $start . '" or asl.LSTIME is null)
            order by asl.LSTIME DESC, a.SEQUID limit 5
        ');

        if (!empty($waitings[0])) {
            foreach ($waitings as $waiting) {
                $data['waitings'][] = $waiting;
            }
        }

        return view(
            'awf-extension::display.shift_management_panel.partials.production_partials.work_center',
            [
                'data' => $data
            ]
        );
    }
}
