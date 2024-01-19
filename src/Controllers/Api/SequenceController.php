<?php

namespace AWF\Extension\Controllers\Api;

use App\Http\Controllers\Controller;
use AWF\Extension\Requests\Api\SequenceShowRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\WORKCENTER;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use AWF\Extension\Helpers\Facades\Controllers\Api\SequenceFacade;

class SequenceController extends Controller
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

    public function show(WORKCENTER $WCSHNA, string|null $pillar, SequenceShowRequest $request): JsonResponse
    {
        return $this->facade->show($request, [$WCSHNA, $pillar]);
    }
}
