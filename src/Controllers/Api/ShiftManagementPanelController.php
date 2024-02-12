<?php

namespace AWF\Extension\Controllers\Api;

use AWF\Extension\Helpers\Facades\Controllers\Api\ShiftManagementPanelFacade;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftManagementPanelController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new ShiftManagementPanelFacade();
    }

    public function default(string $dashboardId): JsonResponse
    {
        return $this->facade->default($dashboardId);
    }
}