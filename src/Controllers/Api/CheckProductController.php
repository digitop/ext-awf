<?php

namespace AWF\Extension\Controllers\Api;

use AWF\Extension\Helpers\Facades\Controllers\Api\CheckProductFacade;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use App\Http\Controllers\Controller;
use AWF\Extension\Requests\Api\CheckProductCheckRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckProductController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new CheckProductFacade();
    }

    public function check(CheckProductCheckRequest $request): JsonResponse
    {
        return $this->facade->check($request);
    }
}
