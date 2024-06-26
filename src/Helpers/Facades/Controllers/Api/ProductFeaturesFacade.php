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
use App\Models\DASHBOARD_MODULE_SETTINGS;

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
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

        $productIn = [];

        $workCenterId = DASHBOARD::where('DHIDEN', '=', $request->dashboard)->with('operatorPanels')->first();

        if (!empty($workCenterId) && array_key_exists(0, $workCenterId->operatorPanels->all())) {
            $workCenterId = $workCenterId->operatorPanels[0]?->WCSHNA;
        }

        if (!empty($workCenterId)) {
            $workCenter = WORKCENTER::where('WCSHNA', '=', $workCenterId)->first();
        }

        if (empty($workCenter)) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [
                        'data' => [],
                        'status' => 'default',
                        'orderCode' => null,
                        'name' => null,
                        'opshna' => null,
                    ]
                ),
                Response::HTTP_OK
            )
            );
        }

        $queryString = '
            select a.PRCODE, a.ORCODE,r.OPSHNA, a.SEQUID, a.SEPSEQ, a.SEARNU, a.SESIDE, a.SEPILL, a.SEPONR,
                   a.SEINPR, p.PRNAME, p.PRCODE
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
            where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
            '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
            ' order by a.SEQUID limit 1'
        ;

        $sequence = DB::connection('custom_mysql')->select($queryString);

        if (array_key_exists(0, $sequence)) {
            $sequence = $sequence[0];
        }

        if (empty($sequence)) {
            $status = 'waiting';
        }

        $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
            'WFVALU' => $status,
        ]);

        if (!empty($sequence)) {
            $productInSequence = DB::connection('custom_mysql')->select('
            select distinct PRCODE from AWF_SEQUENCE a
                join ' . $database . '.REPNO r on a.ORCODE = r.ORCODE
            where SEPILL = "' . $sequence->SEPILL . '" and SESIDE = "' . $sequence->SESIDE .
                '" and r.WCSHNA = "' . $workCenter->WCSHNA . '"'
            );

            foreach ($productInSequence as $item) {
                if (!in_array($item->PRCODE, $productIn, true)) {
                    $productIn[] = $item->PRCODE;
                }
            }
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                [
                    'data' => (new ProductColorsResponse(
                        PRODUCT::whereNull('DELDAT')->where('PRACTV', '=', 1)
                            ->whereIn('PRCODE', $productIn)
                            ->get(),
                        $workCenter
                    ))->setSequence($sequence)->generate(),
                    'status' => $workCenter?->features()->where('WFSHNA', '=', 'OPSTATUS')->first()->WFVALU,
                    'orderCode' => $sequence?->ORCODE ?? null,
                    'name' => $sequence?->PRNAME ?? null,
                    'opshna' => $sequence?->OPSHNA ?? null,
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
        $database = config('database.connections.mysql.database');
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

        $workCenterId = DASHBOARD::where('DHIDEN', '=', $request->dashboard)->with('operatorPanels')->first();

        if (!empty($workCenterId) && array_key_exists(0, $workCenterId->operatorPanels->all())) {
            $workCenterId = $workCenterId->operatorPanels[0]?->WCSHNA;
        }

        if (!empty($workCenterId)) {
            $workCenter = WORKCENTER::where('WCSHNA', '=', $workCenterId)->first();
        }

        if (empty($workCenter)) {
            return new CustomJsonResponse(
                new JsonResponseModel(
                    new ResponseData(false, [], 'Nincs beállítva munkaállomás'),
                    Response::HTTP_OK
                )
            );
        }

        $queryString = '
            select a.PRCODE, a.ORCODE,r.OPSHNA, r.RNREPN, a.SEQUID, a.SEPSEQ, a.SEARNU, a.SESIDE, a.SEPILL, a.SEPONR,
                   a.SEINPR, p.PRNAME, p.PRCODE
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
            where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
            '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
            ' order by a.SEQUID limit 1';

        $sequenceLog = DB::connection('custom_mysql')->select($queryString);

        if (!array_key_exists(0, $sequenceLog) || empty($sequenceLog[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.no_new_data_available')
                ),
                Response::HTTP_OK
            ));
        }

        $sequenceLog = $sequenceLog[0];

        $product = PRODUCT::where('PRCODE', '=', $sequenceLog->PRCODE)->first();

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

        if (trim($product->PRCODE) !== trim($request->productCode)) {
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

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                [
                    'RNREPN' => $sequenceLog->RNREPN,
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
