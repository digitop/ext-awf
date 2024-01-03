<?php

namespace AWF\Extension\Controllers\Api;

use App\Http\Controllers\Controller;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;

class MakeOrderController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new SequenceFacade();
    }

    public function create(Request $request): JsonResponse
    {
        return $this->facade->create($request);
    }
}
