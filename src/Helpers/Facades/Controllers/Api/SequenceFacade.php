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
        $sequences = AWF_SEQUENCE::where('SEINPR', '=', 0)
            ->orderBy('SEPILL', 'DESC')
            ->orderBy('SEQUID', 'ASC')
            ->get();

        if ($sequences === null || !array_key_exists(0, $sequences->all()) || empty($sequences[0])) {
            return new JsonResponse(
                ['success' => false, 'data' => [], 'message' => __('responses.no_new_data_available')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new JsonResponse(
            [
                'success' => true,
                'data' => (new SequenceFacadeResponse($sequences, $model))->generate(),
                'message' => ''
            ],
            Response::HTTP_OK
        );
    }

    public function show(Model ...$model): JsonResponse|null
    {
        $logs = AWF_SEQUENCE_LOG::where('WCSHNA', '=', $model[0]->WCSHNA)
            ->whereNull('LSTIME')
            ->whereNull('LETIME')
            ->get();

        $sequences = new Collection();

        foreach ($logs as $log) {
            $sequence = AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)->first();

            if (!empty($sequence)) {
                $sequences->add($sequence);
            }
        }

        $sequence = $sequences->sort(function ($a, $b) {
            foreach ([['column' => 'SEPILL', 'order' => 'desc'], ['column' => 'SEQUID']] as $sortingInstruction) {

                $a[$sortingInstruction['column']] = $a[$sortingInstruction['column']] ?? '';
                $b[$sortingInstruction['column']] = $b[$sortingInstruction['column']] ?? '';

                if (empty($sortingInstruction['order']) || strtolower($sortingInstruction['order']) === 'asc') {
                    $x = ($a[$sortingInstruction['column']] <=> $b[$sortingInstruction['column']]);
                }
                else {
                    $x = ($b[$sortingInstruction['column']] <=> $a[$sortingInstruction['column']]);
                }

                if ((int)$x !== 0) {
                    return $x;
                }

            }

            return 0;
        })
            ->take(2);

        foreach ($sequence as $item) {
            AWF_SEQUENCE_LOG::where('WCSHNA', '=', $model[0]->WCSHNA)
                ->where('SEQUID', '=', $item->SEQUID)
                ->whereNull('LSTIME')
                ->whereNull('LETIME')
                ->update([
                    'LSTIME' => (new \DateTime()),
                ]);
        }

        return new JsonResponse(
            [
                'success' => true,
                'data' => (new SequenceFacadeResponse($sequence, $model[0]))->generate(),
                'message' => ''
            ],
            Response::HTTP_OK
        );
    }
}
