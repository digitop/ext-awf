<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use App\Models\REPNO;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use App\Models\PRODUCT;
use App\Models\WORKCENTER;
use App\Models\PRWCDATA;
use AWF\Extension\Responses\CustomJsonResponse;
use AWF\Extension\Responses\SequenceFacadeResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;

class MoveSequenceFacade extends Facade
{
    public function store(FormRequest|Request $request, Model|string|null ...$model): JsonResponse|null
    {
        $database = config('database.connections.mysql.database');

        $sequence = AWF_SEQUENCE::where('SEQUID', '=', $request->SEQUID)
            ->first();

        if (empty($sequence)) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.no_new_data_available')
                ),
                Response::HTTP_OK
            ));
        }

        $nextProductWorkCenterData = $this->getNextWorkCenterData($request, $sequence);
        $workCenter = WORKCENTER::where('WCSHNA', '=', $request->WCSHNA)->first();

        $this->move($request, $sequence, $nextProductWorkCenterData);

        if ($nextProductWorkCenterData !== null) {
            $status = 'default';
            $workCenter = WORKCENTER::where('WCSHNA', '=', $nextProductWorkCenterData->WCSHNA)->first();

            $queryString = '
                select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR, p.PRNAME, r.RNREPN
                from AWF_SEQUENCE_LOG asl
                    join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                    join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                    join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
                where asl.LSTIME is null and asl.LETIME is null and a.SEINPR = (r.PORANK - 1) and
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

            publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SEQUENCE_CHANGE/', [
                [
                    "to" => 'dh:' . $workCenter->operatorPanels[0]->dashboard->DHIDEN,
                    "payload" => [
                        "status" => $status,
                        'orderCode' => $sequence2?->ORCODE ?? null,
                        'name' => $sequence2?->PRNAME ?? null,
                    ],
                ]
            ]);
        }

        if (in_array($request->WCSHNA, ['VAB01', 'VAJ01', 'VAB02', 'VAJ02','VBB01', 'VBJ01','VCB01', 'VCJ01'], true)) {
            $status = 'default';
            $workCenter = WORKCENTER::where('WCSHNA', '=', $request->WCSHNA)->first();

            $queryString = '
            select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR, p.PRNAME, r.RNREPN
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                    join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
            where asl.LSTIME is null and asl.LETIME is null and a.SEINPR = (r.PORANK - 1) and
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

            publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SEQUENCE_CHANGE/', [
                [
                    "to" => 'dh:' . $workCenter->operatorPanels[0]->dashboard->DHIDEN,
                    "payload" => [
                        "status" => $status,
                        'orderCode' => $sequence2?->ORCODE ?? null,
                        'name' => $sequence2?->PRNAME ?? null,
                    ],
                ]
            ]);
        }

        if (in_array($request->WCSHNA, ['KAB01', 'KAJ01', 'KAB02', 'KAJ02', 'KBB01', 'KBJ01', 'KCB01', 'KCJ01'], true)) {
            $status = 'default';
            $workCenter2 = WORKCENTER::where('WCSHNA', '=', $request->WCSHNA)->first();

            $queryString = '
            select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR, p.PRNAME, r.RNREPN
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                    join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
            where asl.LSTIME is null and asl.LETIME is null and a.SEINPR = (r.PORANK - 1) and
                asl.WCSHNA = "' . $workCenter2->WCSHNA . '" order by a.SEQUID limit 1'
            ;

            $sequence3 = DB::connection('custom_mysql')->select($queryString);

            if (array_key_exists(0, $sequence3)) {
                $sequence3 = $sequence3[0];
            }

            if (empty($sequence3)) {
                $status = 'waiting';
            }


            $workCenter2->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
                'WFVALU' => $status,
            ]);

            publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SEQUENCE_CHANGE/', [
                [
                    "to" => 'dh:' . $workCenter2->operatorPanels[0]->dashboard->DHIDEN,
                    "payload" => [
                        "status" => $status,
                        'orderCode' => $sequence3?->ORCODE ?? null,
                        'name' => $sequence3?->PRNAME ?? null,
                    ],
                ]
            ]);
        }

        $currentWorkCenter = WORKCENTER::where('WCSHNA', '=', $request->WCSHNA)->first();

        $queryString = '
                select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR,
                       a.SESCRA, a.SEINPR, p.PRNAME, r.RNREPN
                from AWF_SEQUENCE_LOG asl
                    join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                    join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                    join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
                where asl.LSTIME is null and asl.LETIME is null and a.SEINPR = (r.PORANK - 1) and
                    asl.WCSHNA = "' . $currentWorkCenter->WCSHNA . '"' .
            ($request->has('pillar') && !empty($request->pillar) ?
                ' and a.SEPILL = "' . $request->pillar . '"' :
                '') .
            ' order by a.SEQUID limit 1';

        $next = DB::connection('custom_mysql')->select($queryString);

        if (in_array($currentWorkCenter->WCSHNA, ['HAB01', 'HAJ01', 'HBB01', 'HBJ01', 'HCB01', 'HCJ01'], true)) {
            publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SEQUENCE_CHANGE/', [
                [
                    "to" => 'dh:' . $currentWorkCenter->operatorPanels[0]->dashboard->DHIDEN,
                    "payload" => [
                        "status" => $status,
                        'orderCode' => $next?->ORCODE ?? null,
                        'name' => $next?->PRNAME ?? null,
                    ],
                ]
            ]);
        }

        return new CustomJsonResponse(new JsonResponseModel(
            (new ResponseData(
                true,
                (new SequenceFacadeResponse(
                    $sequence,
                    $nextProductWorkCenterData !== null && isset($workCenter) ?
                        $workCenter :
                        null
                ))->generate()
            ))->setNext(
                (new SequenceFacadeResponse(
                    new Collection($next),
                    $currentWorkCenter
                ))->generate()
            ),
            Response::HTTP_OK
        ));
    }

    protected function getNextWorkCenterData(FormRequest|Request $request, Model $sequence): PRWCDATA|null
    {
        $nextProductDetails = DB::select('
            with porank as (select ppd.PORANK, ppd.PFIDEN
                from PRODUCT p
                       join PRWFDATA pfd on p.PRCODE = pfd.PRCODE
                       join PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = "' . $request->WCSHNA . '"
                       join PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                where p.PRCODE = "' . $sequence->PRCODE . '"
            )
            select ppd2.PFIDEN, ppd2.OPSHNA from PROPDATA ppd2
                where ppd2.PORANK = (select porank.PORANK + 1 from porank where ppd2.PFIDEN = porank.PFIDEN)
        ');

        if (!empty($nextProductDetails[0])) {
            $nextProductWorkCenterData = PRWCDATA::where('PFIDEN', '=', $nextProductDetails[0]->PFIDEN)
                ->where('OPSHNA', '=', $nextProductDetails[0]->OPSHNA)
                ->first();
        }

        return $nextProductWorkCenterData ?? null;
    }

    protected function move(FormRequest|Request $request, Model $sequence, Model|null $nextProductWorkCenterData): void
    {
        AWF_SEQUENCE_LOG::where('WCSHNA', '=', $request->WCSHNA)
            ->where('SEQUID', '=', $request->SEQUID)
            ->whereNull('LETIME')
            ->first()
            ?->update([
                'LETIME' => (new \DateTime()),
            ]);

        if ($nextProductWorkCenterData !== null) {
            $repno = REPNO::where([
                ['WCSHNA', $nextProductWorkCenterData->WCSHNA],
                ['ORCODE', $sequence->ORCODE],
                ['RNOLAC', 0]
            ])->first();

            $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $request->SEQUID)
                ->where('WCSHNA', '=', $nextProductWorkCenterData->WCSHNA)
                ->where('RNREPN', '=', $repno?->RNREPN)
                ->first();

            if (empty($sequenceWorkCenter)) {
                AWF_SEQUENCE_WORKCENTER::create([
                    'SEQUID' => $request->SEQUID,
                    'WCSHNA' => $nextProductWorkCenterData->WCSHNA,
                    'RNREPN' => $repno?->RNREPN,
                ]);
            }

            $sequenceLog = AWF_SEQUENCE_LOG::where('SEQUID', '=', $request->SEQUID)
                ->where('WCSHNA', '=', $nextProductWorkCenterData->WCSHNA)
                ->whereNull('LETIME')
                ->first();

            if (empty($sequenceLog)) {
                AWF_SEQUENCE_LOG::create([
                    'SEQUID' => $request->SEQUID,
                    'WCSHNA' => $nextProductWorkCenterData->WCSHNA,
                ]);
            }
        }
    }
}
