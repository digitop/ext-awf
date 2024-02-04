<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\MakeOrder;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Responses\CustomJsonResponse;
use AWF\Extension\Responses\SequenceFacadeResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MakeOrderFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $success = false;

        $sequences = AWF_SEQUENCE::where('SEINPR', '=', 0)
            ->orderBy('SEPILL', 'DESC')
            ->orderBy('SEQUID', 'ASC')
            ->get();

        try {
            foreach ($sequences as $sequence) {
                MakeOrder::makeOrder($sequence);
            }

            $success = true;
        }
        catch (\Exception $exception) {
            $endTime = microtime(true);
            $dataToLog = 'Type: ' . __CLASS__ . "\n";
            $dataToLog .= 'Method: MakeOrder' . "\n";
            $dataToLog .= 'Time: ' . date("Y m d H:i:s") . "\n";
            $dataToLog .= 'Duration: ' . number_format($endTime - LARAVEL_START, 3) . "\n";
            $dataToLog .= 'Output: ' . $exception->getMessage() . "\n";

            Storage::disk('local')->append(
                'logs/awf_make_order_' . Carbon::now()->format('Ymd') . '.log',
                $dataToLog . "\n" . str_repeat("=", 20) . "\n\n"
            );
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                $success
            ),
            Response::HTTP_OK
        ));
    }

}
