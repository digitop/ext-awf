<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Models\SERIALNUMBER;
use App\Models\WORKCENTER;
use App\Models\PRODUCT;

class CheckProductFacade extends Facade
{
    public function check(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $serial = SERIALNUMBER::where('SNSERN', '=', $request->SNSERN)->first();

        if (empty($serial)) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.check.serial_not_found')
                ),
                Response::HTTP_OK
            ));
        }

        $product = PRODUCT::where('PRCODE', '=', $serial->PRCODE)->first();
        $sequences = AWF_SEQUENCE::where('PRCODE', '=', $product->PRCODE)->get();

        $sequence = $sequences->sort(function ($a, $b) {
            foreach ([['column' => 'SEPILL', 'order' => 'desc'], ['column' => 'SEQUID']] as $sortingInstruction) {

                $a[$sortingInstruction['column']] = $a[$sortingInstruction['column']] ?? '';
                $b[$sortingInstruction['column']] = $b[$sortingInstruction['column']] ?? '';

                if (empty($sortingInstruction['order']) || strtolower($sortingInstruction['order']) === 'asc') {
                    $x = ($a[$sortingInstruction['column']] <=> $b[$sortingInstruction['column']]);
                }
                else {
                    $x = ($b[$sortingInstruction['column']] <=> $a[$sortingInstruction['column']]);
                }

                if ($x !== 0) {
                    return $x;
                }

            }

            return 0;
        })
            ->take(1);

        $workCenter = WORKCENTER::where(
            'WCSHNA',
            '=',
            DASHBOARD::where('DHIDEN', '=', $request->dashboard)->first()->operatorPanels[0]->WCSHNA
        )->first();

        $sequenceLog = AWF_SEQUENCE_LOG::where('WCSHNA', '=', $workCenter->WCSHNA)
            ->where('SEQUID', '=', $sequence->SEQUID)
            ->whereNull('LSTIME')
            ->whereNull('LETIME')
            ->fist();

        if (empty($sequenceLog)) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.check.not_next_product')
                ),
                Response::HTTP_OK
            ));
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
            ),
            Response::HTTP_OK
        ));
    }
}