<?php

namespace AWF\Extension\Controllers\Api;

use AWF\Extension\Helpers\Facades\Controllers\Api\AutoScrapFacade;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\WORKCENTER;

class AutoScrapController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new AutoScrapFacade();
    }

    public function scrap(WORKCENTER $WCSHNA): JsonResponse
    {
        return $this->facade->scrap($WCSHNA);
    }
}
