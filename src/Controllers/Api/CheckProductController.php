<?php

namespace AWF\Extension\Controllers\Api;

use AWF\Extension\Helpers\Facades\Controllers\Api\CheckProductFacade;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckProductController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new CheckProductFacade();
    }

    public function check(Request $request): JsonResponse
    {
        return $this->facade->create($request);
    }
}
