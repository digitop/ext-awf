<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use AWF\Extension\Responses\CustomJsonResponse;
use AWF\Extension\Responses\ProductColorsResponse;
use AWF\Extension\Responses\ProductFeaturesResponse;
use AWF\Extension\Responses\ProductMaterialsResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Models\PRODUCT;
use App\Models\DASHBOARD;
use App\Models\WORKCENTER;

class ProductFeaturesFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new ProductFeaturesResponse(
                    PRODUCT::whereNull('DELDAT')->where('PRACTV', '=', 1)->get()
                ))->generate()
            ),
            Response::HTTP_OK
        )
        );
    }

    public function show(Request|FormRequest|null $request = null, Model|string|null ...$model): JsonResponse|null
    {
        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new ProductFeaturesResponse(
                    PRODUCT::whereNull('DELDAT')
                        ->where('PRACTV', '=', 1)
                        ->where('PRCODE', '=', $request->productCode)
                        ->get()
                ))->generate()
            ),
            Response::HTTP_OK
        )
        );
    }

    public function colors(Request|FormRequest|null $request = null): JsonResponse|null
    {
        $status = 'default';
        $database = config('database.connections.mysql.database');

        $workCenter = WORKCENTER::where(
            'WCSHNA',
            '=',
            DASHBOARD::where('DHIDEN', '=', $request->dashboard)->first()->operatorPanels[0]->WCSHNA
        )->first();

        $queryString = '
        select PRCODE, ORCODE from AWF_SEQUENCE a
            join AWF_SEQUENCE_WORKCENTER asw on asw.SEQUID = a.SEQUID
            join AWF_SEQUENCE_LOG asl on asl.SEQUID = a.SEQUID
            where a.SEINPR = 1 and asl.LSTIME is null and asl.LETIME is null and asw.WCSHNA = "' .
            $workCenter->WCSHNA . '"';

        $sequence = DB::connection('custom_mysql')->select($queryString);

        if (array_key_exists(0, $sequence)) {
            $sequence = $sequence[0];
        }

        $status = 'default';
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';
        $workCenter = WORKCENTER::where('WCSHNA', '=', $workCenter->WCSHNA)->first();

        $queryString = '
            select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
            where (asl.LSTIME is null or asl.LSTIME > "' . $start . '") and asl.LETIME is null and a.SEINPR = (ppd.PORANK - 1) and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '" order by a.SEQUID limit 1'
        ;

        $sequence2 = DB::connection('custom_mysql')->select($queryString);

        if (array_key_exists(0, $sequence2)) {
            $sequence2 = $sequence2[0];
        }

        if (empty($sequence2)) {
            $status = 'waiting';
        }

        $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
            'WFVALU' => $status,
        ]);

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                [
                    'data' => (new ProductColorsResponse(
                        PRODUCT::whereNull('DELDAT')->where('PRACTV', '=', 1)->get(),
                        $workCenter
                    ))->setSequence($sequence)->generate(),
                    'status' => $workCenter?->features()->where('WFSHNA', '=', 'OPSTATUS')->first()->WFVALU,
                    'orderCode' => $sequence?->ORCODE ?? null,
                ]
            ),
            Response::HTTP_OK
        ));
    }

    public function materials(): JsonResponse|null
    {
        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new ProductMaterialsResponse(
                    PRODUCT::whereNull('DELDAT')->where('PRACTV', '=', 1)->get()
                ))->generate()
            ),
            Response::HTTP_OK
        )
        );
    }

    public function check(Request|FormRequest|null $request = null): JsonResponse|null
    {
        $workCenter = WORKCENTER::where(
            'WCSHNA',
            '=',
            DASHBOARD::where('DHIDEN', '=', $request->dashboard)->first()->operatorPanels[0]->WCSHNA
        )->first();

        $sequenceLog = AWF_SEQUENCE_LOG::where('WCSHNA', '=', $workCenter->WCSHNA)
            ->whereNull('LSTIME')
            ->whereNull('LETIME')
            ->orderBy('SEQUID')
            ->first();

        if (empty($sequenceLog)) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.no_new_data_available')
                ),
                Response::HTTP_OK
            ));
        }

        $sequence = AWF_SEQUENCE::where('SEQUID', '=', $sequenceLog->SEQUID)->first();

        $product = PRODUCT::where('PRCODE', '=', $sequence->PRCODE)->first();

        if ($product->features()->where('FESHNA', '=', 'SZASZ')->first() === null) {
            $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
                'WFVALU' => 'fail',
            ]);

            return new CustomJsonResponse(
                new JsonResponseModel(
                    new ResponseData(false, [], __('response.check.empty_color')),
                    Response::HTTP_OK
                )
            );
        }

        $color = $product->features()->where('FESHNA', '=', 'SZASZ')->first();

        if ($color->FEVALU !== $request->color) {
            $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
                'WFVALU' => 'fail',
            ]);

            return new CustomJsonResponse(
                new JsonResponseModel(
                    new ResponseData(false, [], __('response.check.wrong_color')),
                    Response::HTTP_OK
                )
            );
        }

        $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
            'WFVALU' => 'success',
        ]);

        $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequence->SEQUID)
            ->where('WCSHNA', '=', $workCenter->WCSHNA)
            ->first();

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                [
                    'RNREPN' => $sequenceWorkCenter->RNREPN,
                    'PRCODE' => $product->PRCODE,
                    'SNCOUN' => 1,
                    'SNRDCN' => 1,
                    'SNSERN' => null,
                    'parentSNSERN' => false,
                    'subProduct' => false,
                ]
            ),
            Response::HTTP_OK
        ));
    }
}
