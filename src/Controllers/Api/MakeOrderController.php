<?php

namespace AWF\Extension\Controllers\Api;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\Facades\Controllers\Api\MakeOrderFacade;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MakeOrderController extends Controller
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
}
