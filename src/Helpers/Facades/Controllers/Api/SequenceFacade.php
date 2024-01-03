<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use App\Models\WORKCENTER;
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

        $sequence = $sequences->sortBy('SEQUID')->take(2);

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

    public function store(FormRequest|Request $request, Model|string|null ...$model): JsonResponse|null
    {
        $sequence = AWF_SEQUENCE::where('SEQUID', '=', $request->SEQUID)
            ->where('SEEXPI', '>=', (new \DateTime())->format('Y-m-d'))
            ->first();

        $product = PRODUCT::where('PRCODE', '=', $sequence->PRCODE)->first();
        $productWorkCenterData = $product->workflows[0]->operationWorkcenters()
            ->where('WCSHNA', '=', $request->WCSHNA)
            ->first();
        $productOperation = $product->workflows[0]->operations()
            ->where('PFIDEN', '=', $productWorkCenterData->PFIDEN)
            ->where('OPSHNA', '=', $productWorkCenterData->OPSHNA)
            ->first();

        $nextProductOperation = $product->workflows[0]->operations()
            ->where('PFIDEN', '=', $productWorkCenterData->PFIDEN)
            ->where('PORANK', '=', (int)$productOperation->PORANK + 1)
            ->first();
        $nextProductWorkCenterData = $product->workflows[0]->operationWorkcenters()
            ->where('PFIDEN', '=', $nextProductOperation->PFIDEN)
            ->where('OPSHNA', '=', $nextProductOperation->OPSHNA)
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
                'LETIME' => (new \DateTime()),
            ]);

        AWF_SEQUENCE_WORKCENTER::create([
            'SEQUID' => $request->SEQUID,
            'WCSHNA' => $nextProductWorkCenterData->WCSHNA,
        ]);

        AWF_SEQUENCE_LOG::create([
            'SEQUID' => $request->SEQUID,
            'WCSHNA' => $nextProductWorkCenterData->WCSHNA,
        ]);

        return new JsonResponse(
            [
                'success' => true,
                'data' => (new SequenceFacadeResponse(
                    $sequence,
                    WORKCENTER::where('WCSHNA', '=', $nextProductWorkCenterData->WCSHNA)->first()
                ))->generate(),
                'message' => ''
            ],
            Response::HTTP_OK
        );
    }
}
