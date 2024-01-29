<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web;

use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\View as IlluminateView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftManagementPanelFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null,
                           Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        $type = 'start';

        if (!empty($request) && $request->has('type')) {
            $type = $request->type;
        }

        return view('awf-extension::display/shift_management_panel.default', [
            'type' => $type
        ]);
    }
}
