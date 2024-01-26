<?php

namespace AWF\Extension\Controllers\Api;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\Facades\Controllers\Api\ScrapFacade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use App\Events\Dashboard\ProductQualifie;

class ScrapController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new ScrapFacade();
    }

    public function index(ProductQualifie $event): JsonResponse
    {
        return $this->facade->create($event);
    }
}
