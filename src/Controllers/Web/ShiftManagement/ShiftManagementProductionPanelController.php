<?php

namespace AWF\Extension\Controllers\Web\ShiftManagement;

use App\Http\Controllers\Controller;
use AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement\ShiftManagementProductionPanelFacade;
use AWF\Extension\Interfaces\WebControllerFacadeInterface;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View as IlluminateView;
use App\Models\WORKCENTER;

class ShiftManagementProductionPanelController extends Controller
{
    protected WebControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new ShiftManagementProductionPanelFacade();
    }
    public function create(Request $request): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        return $this->facade->create($request);
    }

    public function show(Request $request, string $WCSHNA): Application|Factory|View|ContractsApplication|null
    {
        return $this->facade->show($request, WORKCENTER::where('WCSHNA', $WCSHNA)->first());
    }

    public function data(string $WCSHNA): array
    {
        return $this->facade->data(WORKCENTER::where('WCSHNA', $WCSHNA)->first());
    }
}
