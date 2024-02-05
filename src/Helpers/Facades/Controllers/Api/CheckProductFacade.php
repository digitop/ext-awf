<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Models\SERIALNUMBER;
use App\Models\WORKCENTER;
use App\Models\REPNO;
use App\Models\DASHBOARD;
use Symfony\Component\HttpFoundation\Response;

class CheckProductFacade extends Facade
{
    public function check(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $workCenter = WORKCENTER::where(
            'WCSHNA',
            '=',
            DASHBOARD::where('DHIDEN', '=', $request->dashboard)->first()->operatorPanels[0]->WCSHNA
        )->first();

        $sequenceLog = AWF_SEQUENCE_LOG::where('WCSHNA', '=', $workCenter->WCSHNA)
            ->whereNull('LSTIME')
            ->whereNull('LETIME')
            ->first();

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

        $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('WCSHNA', '=', $workCenter->WCSHNA)
            ->where('SEQUID', '=', $sequenceLog->SEQUID)
            ->first();

        $serial = SERIALNUMBER::where('SNSERN', '=', $request->serial)
            ->first();

        if (empty($serial) || (int)$serial->SNCYID > 0) {
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
                [
                    'orderCode' => AWF_SEQUENCE::where('SEQUID', '=', $sequenceWorkCenter->SEQUID)->first()->ORCODE,
                ]
            ),
            Response::HTTP_OK
        ));
    }
}