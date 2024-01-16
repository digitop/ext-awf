<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Responses\CustomJsonResponse;
use AWF\Extension\Responses\ProductColorsResponse;
use AWF\Extension\Responses\ProductFeaturesResponse;
use AWF\Extension\Responses\ProductMaterialsResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Models\PRODUCT;

class ProductFeaturesFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new ProductFeaturesResponse(
                    PRODUCT::whereNull('DELDAT')->where('PRACTV', '=', 1)->get()
                ))->generate()
            ),
            Response::HTTP_OK
        ));
    }

    public function show(Request|FormRequest|null $request = null, Model ...$model): JsonResponse|null
    {
        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new ProductFeaturesResponse(
                    PRODUCT::whereNull('DELDAT')
                        ->where('PRACTV', '=', 1)
                        ->where('PRCODE', '=', $request->productCode)
                        ->get()
                ))->generate()
            ),
            Response::HTTP_OK
        ));
    }

    public function colors(): JsonResponse|null
    {
        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new ProductColorsResponse(
                    PRODUCT::whereNull('DELDAT')->where('PRACTV', '=', 1)->get()
                ))->generate()
            ),
            Response::HTTP_OK
        ));
    }

    public function materials(): JsonResponse|null
    {
        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new ProductMaterialsResponse(
                    PRODUCT::whereNull('DELDAT')->where('PRACTV', '=', 1)->get()
                ))->generate()
            ),
            Response::HTTP_OK
        ));
    }

    public function check(Request|FormRequest|null $request = null): JsonResponse|null
    {
        $product = PRODUCT::where('PRCODE', '=', $request->productCode)
            ->where('PRACTV', '=', 1)
            ->first();

        if ($product->features()->where('FESHNA', '=', 'SZASZ')->first() === null) {
            return new CustomJsonResponse(
                new JsonResponseModel(
                    new ResponseData(true, [], __('response.check.empty_color')),
                    Response::HTTP_BAD_REQUEST
                )
            );
        }

        $color = $product->features()->where('FESHNA', '=', 'SZASZ')->first();

        if ($color->FEVALU !== $request->color) {
            return new CustomJsonResponse(
                new JsonResponseModel(
                    new ResponseData(true, [], __('response.check.wrong_color')),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                )
            );
        }

        return new CustomJsonResponse(new JsonResponseModel(new ResponseData(true), Response::HTTP_OK));
    }
}
