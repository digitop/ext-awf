<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
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
use App\Models\DASHBOARD;
use App\Models\WORKCENTER;

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

    public function show(Request|FormRequest|null $request = null, Model|string|null ...$model): JsonResponse|null
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

    public function colors(Request|FormRequest|null $request = null): JsonResponse|null
    {
        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new ProductColorsResponse(
                    PRODUCT::whereNull('DELDAT')->where('PRACTV', '=', 1)->get(),
                    WORKCENTER::where(
                        'WCSHNA',
                        '=',
                        DASHBOARD::where('DHIDEN', '=', $request->dashboard)->first()->operatorPanels[0]->WCSHNA
                    )->first()
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
        $workCenter = WORKCENTER::where(
        'WCSHNA',
        '=',
        DASHBOARD::where('DHIDEN', '=', $request->dashboard)->first()->operatorPanels[0]->WCSHNA
        )->first();

        $sequenceLog = AWF_SEQUENCE_LOG::where('WCSHNA', '=', $workCenter->WCSHNA)
            ->whereNull('LSTIME')
            ->whereNull('LETIME')
            ->orderBy('SEQUID')
            ->first();

        $sequence = AWF_SEQUENCE::where('SEQUID', '=', $sequenceLog->SEQUID)->first();

        $product = PRODUCT::where('PRCODE', '=', $sequence->PRCODE)->first();

        if ($product->features()->where('FESHNA', '=', 'SZASZ')->first() === null) {
            $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
                'WFVALU' => 'fail',
            ]);

            return new CustomJsonResponse(
                new JsonResponseModel(
                    new ResponseData(false, [], __('response.check.empty_color')),
                    Response::HTTP_BAD_REQUEST
                )
            );
        }

        $color = $product->features()->where('FESHNA', '=', 'SZASZ')->first();

        if ($color->FEVALU !== $request->color) {
            $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
                'WFVALU' => 'fail',
            ]);

            return new CustomJsonResponse(
                new JsonResponseModel(
                    new ResponseData(false, [], __('response.check.wrong_color')),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                )
            );
        }

        $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
            'WFVALU' => 'success',
        ]);

        return new CustomJsonResponse(new JsonResponseModel(new ResponseData(true), Response::HTTP_OK));
    }
}
