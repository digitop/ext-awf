<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use AWF\Extension\Responses\SequenceFacadeResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\PRODUCT;

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
                ['success' => false, 'data' => [], 'error' => __('responses.no_new_data_available')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new JsonResponse(
            [
                'success' => true,
                'data' => (new SequenceFacadeResponse($sequences, $model))->generate(),
                'error' => ''
            ],
            Response::HTTP_OK
        );
    }

    public function show(Model ...$model): JsonResponse|null
    {
        $logs = AWF_SEQUENCE_LOG::where('WCSHNA', '=', $model[0]->WCSHNA)
            ->where('LSTIME', '>=', (new \DateTime())->format('Y-m-d'))
            ->whereNull('LETIME')
            ->get();

        $sequences = new Collection();

        foreach ($logs as $log) {
            $sequence = AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)->first();

            if (!empty($sequence)) {
                $sequences->add($sequence);
            }
        }

        return new JsonResponse(
            [
                'success' => true,
                'data' => (new SequenceFacadeResponse($sequences->sortBy('SEQUID')->take(2), $model[0]))->generate(),
                'error' => ''
            ],
            Response::HTTP_OK
        );
    }

    public function store(FormRequest|Request $request, Model|string|null ...$model): JsonResponse|null
    {
        $sequence = AWF_SEQUENCE::where('SEQUID', '=', $request->SEQUID)->first();
        $product = PRODUCT::where('PRCODE', '=', $sequence->PRCODE)
            ->where('SEEXPI', '>=', (new \DateTime())->format('Y-m-d'))
            ->first();

        if ($sequence->SEINPR === false) {
            $sequence->update([
                'SEINPR' => true,
            ]);
        }

        AWF_SEQUENCE_LOG::where('WCSHNA', '=', $request->WCSHNA)
            ->where('SEQUID', '=', $request->SEQUID)
            ->first()
            ?->update([
                'LETIME' => (new \DateTime())
            ]);

        //todo save data to AWF_SEQUENCE_WORKCENTER table

        return new JsonResponse(
            [
                'success' => true,
                'data' => (new SequenceFacadeResponse($sequence, $model[0]))->generate(),
                'error' => ''
            ],
            Response::HTTP_OK
        );
    }
}
