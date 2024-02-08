<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web;

use AWF\Extension\Events\NextProductEvent;
use AWF\Extension\Helpers\Models\NextProductEventModel;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\View as IlluminateView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WelderPanelFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null,
                           Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        $default = true;

        if ($request?->has('default')) {
            $default = $request?->default === 'true';
        }
        return view('awf-extension::display/welder_panel', [
            'default' => $default,
            'nextSequence' => new NextProductEventModel(),
        ]);
    }

    public function default(): void
    {
        event(new NextProductEvent(
                (new NextProductEventModel())->get()
            )
        );
    }
}
