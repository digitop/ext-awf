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
    /**
     * Display ajax response.
     *
     * @return JsonResponse
     */
    public function ajax(): JsonResponse
    {
        $dataTables =  new EloquentDataTable($this->query());

        return $dataTables->make(true);
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return QueryBuilder|EloquentBuilder
     */
    public function query(): QueryBuilder|EloquentBuilder
    {
        $records = AWF_SEQUENCE::where('SEINPR', '=', 0)
            ->orderBy('SEPILL', 'DESC')
            ->orderBy('SEQUID', 'ASC')
            ->get();

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
                ['data' => 'CRNAME', 'name' => 'CRNAME', 'title' => __('semperform-logistic.fields.CRNAME')],
                ['data' => 'actions', 'name' => 'actions', 'title' => __('button.operations'), 'class' => 'text-right all', 'orderable' => false, 'searchable' => false],
            ])
            ->parameters(getStandardParameters(false));
    }
}
