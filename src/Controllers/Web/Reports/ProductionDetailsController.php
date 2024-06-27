<?php

namespace AWF\Extension\Controllers\Web\Reports;

use App\Helpers\ReportFilter;
use App\Http\Controllers\Controller;
use App\Models\SERIALNUMBER;
use App\Models\SERIALNUMBER_LOG;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use AWF\Extension\Requests\Web\Reports\ProductionDetailsShowRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ProductionDetailsController extends Controller
{
    /**
     * @return Factory|View
     */
    public function index()
    {
        return returnFn(
            'modules/report/index',
            [
                'pageTitle' => __('menu.ext.awf.porsche.report.productionDetails'),
                'documentationUrl' => '',
                'reportFilters' => [
                    new ReportFilter([
                        'id' => 'filterA',
                        'label' => __('common.barcode'),
                        'type' => 'textFilter',
                        'required' => false,
                        'formName' => 'serial',
                        'values' => '',
                        'placeholder' => __('placeholder.barcode')
                    ]),
                    new ReportFilter([
                        'id' => 'filterB',
                        'label' => __('display.data.shift-sequence.porscheOrderNumber'),
                        'type' => 'textFilter',
                        'required' => false,
                        'formName' => 'porscheOrderNumber',
                        'values' => '',
                        'placeholder' => ''
                    ]),
                    new ReportFilter([
                        'id' => 'filterC',
                        'label' => __('display.data.shift-sequence.porscheSequenceNumber'),
                        'type' => 'textFilter',
                        'required' => false,
                        'formName' => 'porscheSequenceNumber',
                        'values' => '',
                        'placeholder' => ''
                    ]),
                    new ReportFilter([
                        'id' => 'filterD',
                        'label' => __('display.data.shift-sequence.articleNumber'),
                        'type' => 'textFilter',
                        'required' => false,
                        'formName' => 'productCode',
                        'values' => '',
                        'placeholder' => ''
                    ]),
                ],
                'reportFilterFormUrl' => 'report/awfProductionDetails'
            ]
        );
    }

    /**
     * @param reportRequestA $request
     * @return Factory|View
     */
    public function show(ProductionDetailsShowRequest $request): Factory|View
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
                "reportFilters" => [
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
                        'values' => !empty($request->porscheOrderNumber) ? $request->porscheOrderNumber : '',
                        'placeholder' => ''
                    ]),
                    new ReportFilter([
                        'id' => 'filterC',
                        'label' => __('display.data.shift-sequence.porscheSequenceNumber'),
                        'type' => 'textFilter',
                        'required' => false,
                        'formName' => 'porscheSequenceNumber',
                        'values' => !empty($request->porscheSequenceNumber) ? $request->porscheSequenceNumber : '',
                        'placeholder' => ''
                    ]),
                    new ReportFilter([
                        'id' => 'filterD',
                        'label' => __('display.data.shift-sequence.articleNumber'),
                        'type' => 'textFilter',
                        'required' => false,
                        'formName' => 'productCode',
                        'values' => !empty($request->productCode) ? $request->productCode : '',
                        'placeholder' => ''
                    ]),
                ],
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
}
