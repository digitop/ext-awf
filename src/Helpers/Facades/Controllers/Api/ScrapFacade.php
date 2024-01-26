<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\MakeOrder;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Http\FormRequest;
use App\Events\Dashboard\ProductQualified;
use App\Models\QUALREPHEAD;
use App\Models\REPNO;

class ScrapFacade extends Facade
{
    public function index(ProductQualified $event): JsonResponse
    {
        if ($event->scrapReport !== false) {
            $qualification = QUALREPHEAD::where('QRIDEN', '=', $event->scrapReport)->first();

            $sequence = AWF_SEQUENCE::where('ORCODE', '=', $qualification->ORCODE)->where('SEINPR', '=', 1)->first();

            $sequence?->update([
                'SEINPR' => 0,
                'SESCRA' => true,
            ]);

            AWF_SEQUENCE_LOG::create([
                'SEQUID' => $sequence->SEQUID,
                'WCSHNA' => 'EL01',
            ]);

            MakeOrder::makeOrder($sequence);

            $sequence->refresh();

            AWF_SEQUENCE_WORKCENTER::create([
                'SEQUID' => $sequence->SEQUID,
                'WCSHNA' => 'EL01',
                'RNREPN' => REPNO::where('ORCODE', '=', $sequence->ORCODE)->where('WCSHNA', '=', 'EL01')->first()->RNREPN
            ]);

            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    true,
                ),
                Response::HTTP_OK
            ));
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                false,
                [],
                __('response.unprocessable_entity')
            ),
            Response::HTTP_OK
        ));
    }
}
