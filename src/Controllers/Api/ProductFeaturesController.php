<?php

namespace AWF\Extension\Controllers\Api;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\Facades\Controllers\Api\ProductFeaturesFacade;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use AWF\Extension\Requests\Api\ProductFeatureColorsRequest;
use AWF\Extension\Requests\Api\ProductFeaturesCheckRequest;
use AWF\Extension\Requests\Api\ProductFeaturesShowRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductFeaturesController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new ProductFeaturesFacade();
    }

    public function create(Request $request): JsonResponse
    {
        return $this->facade->create($request);
    }

    public function show(ProductFeaturesShowRequest $request): JsonResponse
    {
        return $this->facade->show($request);
    }

    public function colors(ProductFeatureColorsRequest|null $request = null): JsonResponse
    {
        return $this->facade->colors($request);
    }

    public function materials(Request $request): JsonResponse
    {
        return $this->facade->materials($request);
    }

    public function check(ProductFeaturesCheckRequest $request): JsonResponse
    {
        return $this->facade->check($request);
    }
}
