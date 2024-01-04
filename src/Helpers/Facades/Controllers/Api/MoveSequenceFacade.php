<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use App\Models\PRODUCT;
use App\Models\WORKCENTER;
use AWF\Extension\Responses\SequenceFacadeResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;

class MoveSequenceFacade extends Facade
{
    public function store(FormRequest|Request $request, Model|string|null ...$model): JsonResponse|null
    {
        $sequence = AWF_SEQUENCE::where('SEQUID', '=', $request->SEQUID)
            ->where('SEEXPI', '>=', (new \DateTime())->format('Y-m-d'))
            ->first();

        $nextProductWorkCenterData = $this->getNextWorkCenterData($request, $sequence);

        $this->move($request, $sequence, $nextProductWorkCenterData);

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

    protected function getNextWorkCenterData(FormRequest|Request $request, Model $sequence): WORKCENTER
    {
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

        return $nextProductWorkCenterData;
    }

    protected function move(FormRequest|Request $request, Model $sequence, Model $nextProductWorkCenterData): void
    {
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
    }
}
