<?php

namespace AWF\Extension\Helpers\DataTable;

use AWF\Extension\Models\AWF_SEQUENCE;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Yajra\Datatables\Html\Builder as HtmlBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class ShiftStartDataTable extends DataTable
{
    protected string|null $pillar = null;

    /**
     * Display ajax response.
     *
     * @return JsonResponse
     */
    public function ajax(): JsonResponse
    {
        $dataTables =  new EloquentDataTable($this->query());

        return $dataTables
            ->addColumn('actions', function ($record) {
                return '<a href="' . route('awf-sequence.set', ['pillar' => $this->getPillar(), 'sequenceId' => $record->SEQUID]) .'">' . __('display.button.setAsStart') . '</a>';
            })
            ->setRowClass(function ($record) {
                $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';
                $database = config('database.connections.mysql.database');

                $queryString = '
                    select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR, a.SESCRA
                    from AWF_SEQUENCE_LOG asl
                        join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                        join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                        join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
                    where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
                    '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                        asl.WCSHNA = "EL01" and a.SEPILL = "' . $this->getPillar() .
                    '" and a.SESIDE = "L" order by a.SEQUID limit 1';

                $sequence = DB::connection('custom_mysql')->select($queryString);

                if (array_key_exists(0, $sequence) && !empty($sequence[0])) {
                    $sequence = $sequence[0];
                }

                return is_object($sequence) && $record->SEQUID === $sequence->SEQUID ? 'alert-success' : '';
            })
            ->rawColumns(['actions'])
            ->make();
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return QueryBuilder|EloquentBuilder
     */
    public function query(): QueryBuilder|EloquentBuilder
    {
        $records = AWF_SEQUENCE::selectRaw('AWF_SEQUENCE.*')
            ->join('AWF_SEQUENCE_LOG as asl', function ($join) {
                $join->on('asl.SEQUID', '=', 'AWF_SEQUENCE.SEQUID');
            })
            ->where('asl.WCSHNA', '=', 'EL01')
            ->where(function ($query) {
                $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

                $query->whereNull('asl.LSTIME')->orWhere('asl.LSTIME', '>=', $start);
            })
            ->where('SESIDE', 'L');

         if ($this->getPillar() !== null) {
             $records->where('AWF_SEQUENCE.SEPILL', '=', $this->getPillar());
         }

         $records
             ->orderBy('AWF_SEQUENCE.SEPILL', 'DESC')
             ->orderBy('AWF_SEQUENCE.SEQUID', 'ASC');

        return $this->applyScopes($records);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return HtmlBuilder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->columns([
                ['data' => 'SEQUID', 'name' => 'SEQUID', 'title' => __('awf-extension::display.data.shift-sequence.sequenceId')],
                ['data' => 'SEPONR', 'name' => 'SEPONR', 'title' => __('awf-extension::display.data.shift-sequence.porscheOrderNumber')],
                ['data' => 'SEPSEQ', 'name' => 'SEPSEQ', 'title' => __('awf-extension::display.data.shift-sequence.porscheSequenceNumber')],
                ['data' => 'SEARNU', 'name' => 'SEARNU', 'title' => __('awf-extension::display.data.shift-sequence.articleNumber')],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('button.operations'), 'class' => 'datatable-action', 'orderable' => false, 'searchable' => false],
            ])
            ->parameters([
                'dom' => '<"dtToolbarWrapper"l<"dataTables_topLeftItem"B>f>tr<"dtToolbarWrapper"ip>',
                'responsive' => true,
                'stateSave' => true,
                'ajax' => '/',
                'language' => ['url' => asset('res/DataTable_l18n/' . Session::get('locale') . '.json')],

                'select' => [
                    'style' => 'api',
                    'selector' => 'td:first-child'
                ],
                'buttons' => [
                    [],
                ],
                'order' => [],
            ]);
    }

    public function getPillar(): ?string
    {
        return $this->pillar;
    }

    public function setPillar(?string $pillar): ShiftStartDataTable
    {
        $this->pillar = $pillar;
        return $this;
    }
}
