<?php

namespace AWF\Extension\Controllers\Web\ShiftManagement;

use App\Http\Controllers\Controller;
use App\Models\WORKCENTER;
use AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement\ShiftManagementPanelManualDataRecordFacade;
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

class ShiftManagementPanelManualDataRecordController
{
    protected WebControllerFacadeInterface $facade;

    public function __construct()
    {
        $this->facade = new ShiftManagementPanelManualDataRecordFacade();
    }
    public function create(Request $request): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        return $this->facade->create($request);
    }

    public function show(Request $request, string $WCSHNA): Application|Factory|View|ContractsApplication|null
    {
        return $this->facade->show($request, WORKCENTER::where('WCSHNA', $WCSHNA)->first());
    }

    public function update(Request $request, string $WCSHNA): Application|Factory|View|ContractsApplication|null
    {
        return $this->facade->update($request, WORKCENTER::where('WCSHNA', $WCSHNA)->first());
    }
}
