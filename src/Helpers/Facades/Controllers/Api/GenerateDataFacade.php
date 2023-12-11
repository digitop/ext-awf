<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use Illuminate\Support\Facades\Storage;

class GenerateDataFacade
{
    public function create(Request|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $fileA = Storage::disk('awfSequenceFtp')->get('P992A.csv');

        return null;
    }
}
