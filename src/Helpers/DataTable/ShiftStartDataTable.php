<?php

namespace AWF\Extension\Helpers\DataTable;

use AWF\Extension\Models\AWF_SEQUENCE;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Yajra\Datatables\Html\Builder as HtmlBuilder;
use Illuminate\Http\JsonResponse;

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
        $records = AWF_SEQUENCE::where('SESIDE', 'L')
            ->selectRaw('AWF_SEQUENCE.*')
            ->join('AWF_SEQUENCE_LOG as asl', function ($join) {
                $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

                $join->on('asl.SEQUID', '=', 'AWF_SEQUENCE.SEQUID')
                    ->whereNull('LSTIME')
                    ->orWhere('LSTIME', '>=', $start);
            });

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
            ->parameters(getStandardParameters(false));
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
