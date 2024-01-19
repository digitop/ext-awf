<?php

namespace AWF\Extension\Interfaces;

use Illuminate\Contracts\Foundation\Application as ContractsApplication;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View as ContractsView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

interface ApiControllerFacadeInterface
{
    public function create(Request|FormRequest|null $request = null,
        Model|string|null $model = null
    ): JsonResponse|null;

    public function show(Request|FormRequest|null $request = null, Model|string|null ...$model): JsonResponse|null;

    public function add(Model|null $model = null, Request|null $request = null): JsonResponse|null;

    public function store(Request|FormRequest $request, Model|string|null ...$model): JsonResponse|null;

    public function update(Request|FormRequest $request, Model|string ...$model): JsonResponse|null;

    public function destroy(Request|FormRequest $request, Model|string ...$model): JsonResponse|null;
}
