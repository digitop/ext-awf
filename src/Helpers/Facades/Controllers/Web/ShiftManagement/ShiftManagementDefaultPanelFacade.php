<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web\ShiftManagement;

use AWF\Extension\Helpers\Facades\Controllers\Web\Facade;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View as IlluminateView;

class ShiftManagementDefaultPanelFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null,
                           Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        return view('awf-extension::display.shift_management_panel.partials.default');
    }
}
