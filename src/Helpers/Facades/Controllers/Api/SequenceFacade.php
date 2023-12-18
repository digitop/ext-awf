<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Responses\SequenceFacadeResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Http\FormRequest;

class SequenceFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $sequences = AWF_SEQUENCE::where('SEINPR', '=', 0)->get();

        if ($sequences === null || !array_key_exists(0, $sequences->all()) || empty($sequences[0])) {
            return new JsonResponse(
                ['success' => false, 'data' => [], 'error' => __('responses.no_new_data_available')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $data = (new SequenceFacadeResponse($sequences, $model))->generate();

        return new JsonResponse(
            ['success' => true, 'data' => $data, 'error' => ''],
            Response::HTTP_OK
        );
    }
    public function show(Model ...$model): JsonResponse|null
    {
        if ($model instanceof WORKCENTER) {
            $logs = AWF_SEQUENCE_LOG::where('WCSHNA', '=', $model[0]->WCSHNA)
                ->where('LSTIME', '>=', (new \DateTime())->format('Y-m-d'))
                ->whereNull('LETIME')
                ->get();

            $model = collect();

            foreach ($logs as $log) {
                $sequence = AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)->first();

                if (!empty($sequence)) {
                    $model->add($sequence);
                }
            }
        }

        $data = (new SequenceFacadeResponse($sequences, $model))->generate();

        return new JsonResponse(
            ['success' => true, 'data' => $data, 'error' => ''],
            Response::HTTP_OK
        );
    }

    public function store(FormRequest|Request $request, Model|string ...$model): JsonResponse|null
    {
        $sequences = AWF_SEQUENCE::where('SEINPR', '=', 1)->get();

        return null;
    }
}
