<?php

namespace AWF\Extension\Controllers\Api;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\Facades\Controllers\Api\MoveSequenceFacade;
use AWF\Extension\Requests\Api\MoveSequenceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\WORKCENTER;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;

class MoveSequenceController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new MoveSequenceFacade();
    }

    public function store(MoveSequenceRequest $request): JsonResponse
    {
        return $this->facade->store($request);
    }
}