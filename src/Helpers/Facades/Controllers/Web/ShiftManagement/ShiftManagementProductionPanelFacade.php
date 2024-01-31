<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement;

use App\Models\WORKCENTER;
use AWF\Extension\Helpers\Facades\Controllers\Web\Facade;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
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
        $data = [];
        $now = new \DateTime('2024-01-25 08:43:32');
        $start = $now->format('Y-m-d') . ' 00:00:00';

        $sequences = DB::connection('custom_mysql')->select('
            select asw.WCSHNA, a.PRCODE, a.SEPILL, a.SESIDE, asl.LSTIME, asw.RNREPN, a.ORCODE, a.SEPONR, a.SEPSEQ
            from AWF_SEQUENCE a
            join AWF_SEQUENCE_WORKCENTER asw on asw.SEQUID = a.SEQUID
            left join AWF_SEQUENCE_LOG asl on asl.SEQUID = a.SEQUID and asl.WCSHNA = asw.WCSHNA
            where asl.LSTIME >= "' . $start . '" and asl.LETIME is null
        ');

        foreach ($sequences as $sequence) {
            $product = PRODUCT::where('PRCODE', '=', $sequence->PRCODE)->with('features')->first();

            $data[$sequence->WCSHNA] = [
                'porscheProduct' => $sequence->SEPONR,
                'porscheSequence' => $sequence->SEPSEQ,
                'side' => $sequence->SESIDE,
                'pillar' => $sequence->SEPILL,
                'product' => $sequence->PRCODE,
                'productColor' => $product->features()->where('FESHNA', '=', 'SZASZ')->first()->FEVALU,
                'productMaterial' => ucfirst($product->features()->where('FESHNA', '=', 'SZAA')->first()->FEVALU),
            ];
        }

        return view('awf-extension::display.shift_management_panel.partials.production', ['data' => $data]);
    }
}
