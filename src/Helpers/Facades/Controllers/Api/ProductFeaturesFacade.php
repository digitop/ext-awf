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

        $workCenterId = DASHBOARD::where('DHIDEN', '=', $request->dashboard)->with('operatorPanels')->first();

        if (!empty($workCenterId) && array_key_exists(0, $workCenterId->operatorPanels->all())) {
            $workCenterId = $workCenterId->operatorPanels[0]?->WCSHNA;
        }

        if (!empty($workCenterId)) {
            $workCenter = WORKCENTER::where('WCSHNA', '=', $workCenterId)->first();
        }

        if (empty($workCenter)) {
            $moduleSetting = DASHBOARD_MODULE_SETTINGS::where([
                ['DHIDEN', $request->dashboard],
                ['DMSKEY', 'scrapStationFilter']
            ])
                ->first();

            if ($moduleSetting) {
                $scrapStationFilter = $moduleSetting->DMSVAL;
                // Ha van beallitva ertek a szuroben
                $workCenter = WORKCENTER::find($scrapStationFilter); // Selejt állomás megkeresese
            }
        }

        $queryString = '
            select a.PRCODE, a.ORCODE,r.OPSHNA, a.SEQUID, a.SEPSEQ, a.SEARNU, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR, p.PRNAME
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
            where ((asl.LSTIME is null and a.SEINPR = (ppd.PORANK - 1)) or (asl.LSTIME > "' . $start .
            '" and a.SEINPR = ppd.PORANK)) and asl.LETIME is null and
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
        $workCenter = WORKCENTER::where(
            'WCSHNA',
            '=',
            DASHBOARD::where('DHIDEN', '=', $request->dashboard)->first()->operatorPanels[0]->WCSHNA
        )->first();

        $sequenceLog = AWF_SEQUENCE_LOG::where('WCSHNA', '=', $workCenter->WCSHNA)
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
