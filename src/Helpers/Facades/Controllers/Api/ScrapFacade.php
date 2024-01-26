<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\MakeOrder;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Http\FormRequest;
use App\Events\Dashboard\ProductQualifie;
use App\Models\QUALREPHEAD;

class ScrapFacade extends Facade
{
    public function index(ProductQualifie $event): JsonResponse
    {
        if ($event->scrapReport !== false) {
            $qualification = QUALREPHEAD::where('QRIDEN', '=', $event->scrapReport)->first();

            $sequence = AWF_SEQUENCE::where('ORCODE', '=', $qualification->ORCODE)->where('SEINPR', '=', 1)->first();

            $sequence?->update([
                'SEINPR' => 0,
                'SESCRA' => true,
            ]);

            MakeOrder::makeOrder($sequence);

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
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));
    }
}
