<?php

namespace AWF\Extension\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use AWF\Extension\Helpers\Facades\Controllers\Api\GenerateDataFacade;

class GenerateDataController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new GenerateDataFacade();
    }

    public function create(Request $request): JsonResponse
    {
        return $this->facade->create($request);
    }
}