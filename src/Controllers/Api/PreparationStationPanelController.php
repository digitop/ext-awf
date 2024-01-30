<?php

namespace AWF\Extension\Controllers\Api;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\Facades\Controllers\Api\Facade;
use AWF\Extension\Helpers\Facades\Controllers\Web\PreparationStationPanelFacade;
use AWF\Extension\Interfaces\WebControllerFacadeInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;

class PreparationStationPanelController extends Controller
{
    protected ApiControllerFacadeInterface $facade;
    protected WebControllerFacadeInterface $webFacade;

    public function __construct()
    {
        $this->facade = new Facade();
        $this->webFacade = new PreparationStationPanelFacade();
    }

    public function default(): void
    {
        $this->webFacade->default();
    }
}
