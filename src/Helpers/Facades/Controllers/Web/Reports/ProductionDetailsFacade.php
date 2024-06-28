<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\Reports;

use AWF\Extension\Helpers\Facades\Controllers\Web\Facade;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\View as IlluminateView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Helpers\ReportFilter;
use App\Http\Controllers\Controller;
use App\Models\SERIALNUMBER;
use App\Models\SERIALNUMBER_LOG;

class ProductionDetailsFacade extends Facade
{
    public function index(): Factory|View
    {
        return returnFn(
            'modules/report/index',
            [
                'pageTitle' => __('menu.ext.awf.porsche.report.productionDetails'),
                'documentationUrl' => '',
                'reportFilters' => $this->getFilters(),
                'reportFilterFormUrl' => 'report/awfProductionDetails'
            ]
        );
    }

    public function show(Request|FormRequest|null $request = null,
        Model|string|null ...$model
    ): Application|Factory|View|ContractsApplication|null
    {
        $serial = $request->serial;

        if (empty($request->serial)) {
            $sequence = AWF_SEQUENCE::select('SEQUID', 'ORCODE');

            if (!empty($request->porscheOrderNumber)) {
                $sequence->where('SEPONR', '=', $request->porscheOrderNumber);
            }

            if (!empty($request->porscheSequenceNumber)) {
                $sequence->where('SEARNU', '=', $request->porscheSequenceNumber);
            }

            if (!empty($request->productCode)) {
                $sequence->where('PRCODE', '=', $request->productCode);
            }

            $sequence = $sequence->first();

            $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequence->SEQUID)->where('WCSHNA',
                '<>',
                'EL01'
            )->first();

            $serial = SERIALNUMBER::where('RNREPN', '=', $sequenceWorkCenter->RNREPN)->first()->SNSERN;
        }

        $serialLogs = SERIALNUMBER_LOG::select('SNLNEW', 'SNLOLD')
            ->where(function ($query) use ($serial) {
                $query->where('SNLNEW', $serial)
                    ->orWhere('SNLOLD', $serial);
            })
            ->get();

        if (strpos($serial, '#') !== false) {
            $serial = str_replace('#', '%23', $serial);
        }

        $params = [
            'snsern' => $serial,
        ];

        $otherSnsern = [];

        foreach ($serialLogs as $serialLog) {
            array_push($otherSnsern, str_replace('#', '%23', $serialLog->SNLNEW));
            array_push($otherSnsern, str_replace('#', '%23', $serialLog->SNLOLD));
        }

        $params['otherSnsern'] = $otherSnsern;

        $birtData = birtData($params);

        return returnFn(
            'modules/report/report_data',
            [
                "reportFilters" => $this->getFilters(
                    $serial,
                    $request->porscheOrderNumber,
                    $request->porscheSequenceNumber,
                    $request->productCode
                ),
                "reportFilterFormUrl" => 'report/awfProductionDetails',
                "birt" => [
                    'report' => 'productDetails/productDetails.rptdesign',
                    'language' => substr(Session::get('locale'), 0, 2),
                    'url' => $birtData['url'],
                    'params' => $birtData['params'],
                    'folder' => env('BIRT_FOLDER', 'Oeem'),
                ],
            ]
        );
    }

    protected function getFilters(
        string|null $serial = '',
        string|null $porscheOrderNumber = '',
        string|null $porscheSequenceNumber = '',
        string|null $productCode = ''
    ): array
    {
        return [
            new ReportFilter([
                'id' => 'filterA',
                'label' => __('common.barcode'),
                'type' => 'textFilter',
                'required' => false,
                'formName' => 'serial',
                'values' => $serial,
                'placeholder' => __('placeholder.barcode')
            ]),
            new ReportFilter([
                'id' => 'filterB',
                'label' => __('display.data.shift-sequence.porscheOrderNumber'),
                'type' => 'textFilter',
                'required' => false,
                'formName' => 'porscheOrderNumber',
                'values' => !empty($porscheOrderNumber) ? $porscheOrderNumber : '',
                'placeholder' => ''
            ]),
            new ReportFilter([
                'id' => 'filterC',
                'label' => __('display.data.shift-sequence.porscheSequenceNumber'),
                'type' => 'textFilter',
                'required' => false,
                'formName' => 'porscheSequenceNumber',
                'values' => !empty($porscheSequenceNumber) ? $porscheSequenceNumber : '',
                'placeholder' => ''
            ]),
            new ReportFilter([
                'id' => 'filterD',
                'label' => __('display.data.shift-sequence.articleNumber'),
                'type' => 'textFilter',
                'required' => false,
                'formName' => 'productCode',
                'values' => !empty($productCode) ? $productCode : '',
                'placeholder' => ''
            ]),
        ];
    }
}
