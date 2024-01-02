<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use AWF\Extension\Interfaces\ApiControllerFacadeInterface;

class Facade implements ApiControllerFacadeInterface
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        return null;
    }

    public function show(Model ...$model): JsonResponse|null
    {
        return null;
    }

    public function add(Model|null $model = null, Request|null $request = null): JsonResponse|null
    {
        return null;
    }

    public function store(FormRequest|Request $request, Model|string|null ...$model): JsonResponse|null
    {
        return null;
    }

    public function update(FormRequest|Request $request, string|Model ...$model): JsonResponse|null
    {
        return null;
    }

    public function destroy(Request|FormRequest $request, Model|string ...$model): JsonResponse|null
    {
        return null;
    }
}
