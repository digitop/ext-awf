<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Models\AWF_SEQUENCE;
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

        $data = $this->generateDataArray($sequences, $model);

        return new JsonResponse(
            ['success' => true, 'data' => $data, 'error' => ''],
            Response::HTTP_OK
        );
    }

    protected function generateDataArray(Collection $sequences, Model $workCenter): array
    {
        $data = [];

        foreach ($sequences as $sequence) {

            if ($sequence->workCenter->has($workCenter->WCSHNA)) {
                $data[$sequence->SEPILL][] = [
                    'SEPONR' => $sequence->SEPONR,
                    'SEPSEQ' => $sequence->SEPSEQ,
                    'SEARNU' => $sequence->SEARNU,
                    'SESIDE' => $sequence->SESIDE,
                ];
            }
        }

        return $data;
    }
}
