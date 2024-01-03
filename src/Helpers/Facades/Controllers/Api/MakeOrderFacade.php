<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\MakeOrder;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Responses\SequenceFacadeResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Model;

class MakeOrderFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $sequences = AWF_SEQUENCE::where('SEINPR', '=', 0)
            ->orderBy('SEPILL', 'DESC')
            ->orderBy('SEQUID', 'ASC')
            ->get();

        try {
            foreach ($sequences as $sequence) {
                MakeOrder::makeOrder($sequence);
            }
        }
        catch (\Exception $exception) {
            return new JsonResponse(
                ['success' => false, 'message' => __('response.unprocessable_entity')],
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

}
