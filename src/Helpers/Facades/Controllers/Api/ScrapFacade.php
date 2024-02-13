<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\MakeOrder;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Http\FormRequest;
use App\Events\Dashboard\ProductQualified;
use App\Models\QUALREPHEAD;
use App\Models\SERIALNUMBER;
use App\Models\WORKCENTER;
use App\Models\DASHBOARD;
use App\Models\REPNO;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ScrapFacade extends Facade
{
    public function index(ProductQualified $event): JsonResponse
    {
        if ($event->scrapReport !== false) {
            $qualification = QUALREPHEAD::where('QRIDEN', '=', $event->scrapReport)->first();

            $workCenter = WORKCENTER::where(
                'WCSHNA',
                '=',
                DASHBOARD::where('DHIDEN', '=', $event->DHIDEN)->with('operatorPanels')->first()->operatorPanels[0]->WCSHNA
            )->first();

            $repnos = REPNO::where('ORCODE', '=', $qualification->ORCODE)->get();

            foreach ($repnos as $repno) {
                SERIALNUMBER::where('RNREPN', '=', $repno->RNREPN)->delete();
            }

            $sequence = DB::connection('custom_mysql')->select('
                select a.* from AWF_SEQUENCE a
                    join AWF_SEQUENCE_WORKCENTER asw on a.SEQUID = asw.SEQUID and asw.WCSHNA = "' . $workCenter->WCSHNA . '"
                    join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asw.WCSHNA = asl.WCSHNA
                where asl.LSTIME is not null and asl.LETIME is null and a.SEINPR > 0
            ');

            if (array_key_exists(0, $sequence) && !empty($sequence[0])) {
                $sequence = $sequence[0];
            }

            if (empty($sequence)) {
                return new CustomJsonResponse(new JsonResponseModel(
                    new ResponseData(
                        false,

                    ),
                    Response::HTTP_OK
                ));
            }

            AWF_SEQUENCE::where('SEQUID', '=', $sequence->SEQUID)
                ->where('SEINPR', '=', $sequence->SEINPR)
                ->first()
                ?->update([
                    'SEINPR' => 0,
                    'SESCRA' => true,
                ]);

            AWF_SEQUENCE_LOG::create([
                'SEQUID' => $sequence->SEQUID,
                'WCSHNA' => 'EL01',
            ]);

            MakeOrder::makeOrder(new Collection($sequence));

            $sequence = AWF_SEQUENCE::where('SEQUID', $sequence->SEQUID)
                ->where('SEINPR', '=', 0)
                ->where('SESCRA', 'is', true)
                ->first();

            AWF_SEQUENCE_WORKCENTER::create([
                'SEQUID' => $sequence->SEQUID,
                'WCSHNA' => 'EL01',
                'RNREPN' => REPNO::where('ORCODE', '=', $sequence->ORCODE)->where('WCSHNA', '=', 'EL01')->first()->RNREPN
            ]);

            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    true,
                ),
                Response::HTTP_OK
            ));
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                false,
                [],
                __('response.unprocessable_entity')
            ),
            Response::HTTP_OK
        ));
    }
}
