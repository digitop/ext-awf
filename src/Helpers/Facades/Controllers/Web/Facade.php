<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Web;

use AWF\Extension\Interfaces\WebControllerFacadeInterface;
use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\View as IlluminateView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Facade implements WebControllerFacadeInterface
{
    public function create(Request|FormRequest|null $request = null,
                           Model|string|null        $model = null
    ): Application|Factory|View|IlluminateView|ContractsApplication|null
    {
        return null;
    }

    public function show(Request|FormRequest|null $request = null, Model|string|null ...$model): Application|Factory|View|ContractsApplication|null
    {
        return null;
    }

    public function add(Model|null $model = null, Request|null $request = null): Application|Factory|View|ContractsApplication|null
    {
        return null;
    }

    public function store(Request|FormRequest $request, Model|string|null ...$model): Application|Factory|View|ContractsApplication|null
    {
        return null;
    }

    public function update(Request|FormRequest $request, Model|string ...$model): Application|Factory|View|ContractsApplication|null
    {
        return null;
    }

    public function destroy(Request|FormRequest $request, Model|string ...$model): Application|Factory|View|ContractsApplication|null
    {
        return null;
    }
}