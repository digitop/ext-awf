<?php

namespace AWF\Extension\Controllers\Api;

use App\Http\Controllers\Controller;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use AWF\Extension\Requests\Api\ProductFeaturesShowRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductFeaturesController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new MakeOrderFacade();
    }

    public function create(Request $request): JsonResponse
    {
        return $this->facade->create($request);
    }


    public function show(ProductFeaturesShowRequest $request, WORKCENTER $WCSHNA): JsonResponse
    {
        return $this->facade->show($request, $WCSHNA);
    }
}
