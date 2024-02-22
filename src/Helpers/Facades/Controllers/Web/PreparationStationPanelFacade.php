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

class PreparationStationPanelFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null,
                           Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        $default = true;

        if ($request?->has('default')) {
            $default = $request?->default === 'true';
        }
        return view('awf-extension::display/preparation_station_panel', [
            'default' => $default,
            'nextSequence' => new NextProductEventModel(),
        ]);
    }

    public function default(Request|FormRequest|null $request = null): void
    {
        $eventModel = new NextProductEventModel();

        if ($request->has('fabric_shelf') && (int)$request->fabric_shelf === 1) {
            $eventModel->setIsBecauseFabricShelf(__('response.preparation-panel.because-fabric-shelf'));
        }

        event(new NextProductEvent($eventModel->get()));
    }
}
