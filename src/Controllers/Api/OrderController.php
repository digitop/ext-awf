<?php

namespace AWF\Extension\Controllers\Api;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\Facades\Controllers\Api\OrderFacade;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;
use AWF\Extension\Requests\Api\OrderCheckSerialRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\DASHBOARD_MODULE_SETTINGS;
use App\Models\WORKCENTER;
use App\Models\DASHBOARD;

class OrderController extends Controller
{
    protected ApiControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new OrderFacade();
    }

    public function create(Request $request, string $DHIDEN): JsonResponse
    {
        $workCenter = null;
        $moduleSetting = DASHBOARD_MODULE_SETTINGS::where([
            ['DHIDEN', $DHIDEN],
            ['DMSKEY', 'scrapStationFilter']
        ])
            ->first();

        if ($moduleSetting) {
            $scrapStationFilter = $moduleSetting->DMSVAL;
            $workCenter = WORKCENTER::find($scrapStationFilter);
        }

        return $this->facade->create($request, $workCenter);
    }

    public function store(OrderCheckSerialRequest $request, string $DHIDEN): JsonResponse|null
    {
        return $this->facade->store($request, DASHBOARD::where('DHIDEN', '=', $DHIDEN)->with('operatorPanels')->first());
    }
}
