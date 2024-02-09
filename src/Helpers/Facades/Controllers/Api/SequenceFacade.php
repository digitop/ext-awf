<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Events\NextProductEvent;
use AWF\Extension\Helpers\Facades\Controllers\Web\PreparationStationPanelFacade;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Responses\CustomJsonResponse;
use AWF\Extension\Responses\NextProductEventResponse;
use AWF\Extension\Responses\SequenceFacadeResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Http\FormRequest;

class SequenceFacade extends Facade
{
    public function create(Request|FormRequest|null $request = null, Model|string|null $model = null): JsonResponse|null
    {
        $sequences = AWF_SEQUENCE::where('SEINPR', '=', 0)
            ->orderBy('SEPILL', 'DESC')
            ->orderBy('SEQUID', 'ASC')
            ->get();

        if ($sequences === null || !array_key_exists(0, $sequences->all()) || empty($sequences[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.unprocessable_entity')
                ),
                Response::HTTP_OK
            ));
        }

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new SequenceFacadeResponse($sequences, $model))->generate()
            ),
            Response::HTTP_OK
        ));
    }

    public function show(Request|FormRequest|null $request = null, Model|string|null ...$model): JsonResponse|null
    {
        list($workCenter, $pillar) = $model;

        if (is_string($pillar) && $pillar === 'null') {
            $pillar = null;
        }

        $logs = AWF_SEQUENCE_LOG::where('WCSHNA', '=', $workCenter->WCSHNA)
            ->whereNull('LSTIME')
            ->whereNull('LETIME')
            ->get();

        if ($logs === null || !array_key_exists(0, $logs->all()) || empty($logs[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.no_new_data_available')
                ),
                Response::HTTP_OK
            ));
        }

        $porscheProductNumber = null;

        if (
            $request->has('porscheProductNumber') &&
            is_string($request->porscheProductNumber) &&
            $request->porscheProductNumber !== 'null'
        ) {
            $porscheProductNumber = $request->porscheProductNumber;
        }

        $database = config('database.connections.mysql.database');

        $queryString = '
            select a.PRCODE, a.SEQUID, a.ORCODE,a.SESIDE,a.SEPILL, a.SEPONR, a.SEINPR from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
            where asl.LSTIME is null and asl.LETIME is null and a.SEINPR = (ppd.PORANK - 1) and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
                ($pillar !== null ? ' and a.SEPILL = "' . $pillar .'"' : '') .
                ($request->has('side') ? ' and a.SESIDE = "' . $request->side . '"' : '') .
                ($porscheProductNumber !== null ? ' and a.SEPONR = "' . $porscheProductNumber . '"' : '') .
                ($request->has('limit') ? ' limit ' . $request->limit : '')
        ;

        $sequence = new Collection(DB::connection('custom_mysql')->select($queryString));

        if (empty($sequence[0])) {
            $sequence = new Collection([(object)
                [
                    'SEQUID' => 0,
                    'PRCODE' => 'dummy',
                    'ORCODE' => 'dummy',
                    'SESIDE' => $request?->side,
                    'SEPILL' => $pillar,
                    'SEPONR' => $porscheProductNumber,
                ]
            ]);
        }

        $noChange = false;

        if (
            $request->has('no_change') &&
            (
                (is_string($request->no_change) && $request->no_change == 'true') ||
                (is_bool($request->no_change) && $request->no_change == true)
            )
        ) {
            $noChange = true;
        }

        if ($workCenter->WCSHNA === 'EL01' && $sequence[0]->PRCODE !== 'dummy') {
            if ($noChange) {
                event(new NextProductEvent(
                        (new NextProductEventResponse($sequence, null))->generate()
                    )
                );
            }
            else {
                $side = $request->side == 'L' ? 'R' : 'L';

                $queryString = '
            select a.PRCODE, a.SEQUID, a.ORCODE,a.SESIDE,a.SEPILL, a.SEPONR, a.SEINPR from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
            where asl.LSTIME is null and asl.LETIME is null and a.SEINPR = (ppd.PORANK - 1) and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
                    ($pillar !== null ? ' and a.SEPILL = "' . $pillar .'"' : '') .
                    ($request->has('side') ? ' and a.SESIDE = "' . $side . '"' : '') .
                    ($porscheProductNumber !== null ? ' and a.SEPONR = "' . $porscheProductNumber . '"' : '') .
                    ($request->has('limit') ? ' limit ' . $request->limit : '')
                ;

                $sequence2 = new Collection(DB::connection('custom_mysql')->select($queryString));

                if (empty($sequence2)) {
                    (new PreparationStationPanelFacade())->default();
                }
                else {
                    event(new NextProductEvent(
                            (new NextProductEventResponse($sequence2, null))->generate()
                        )
                    );
                }
            }
        }

        if (!$noChange && $sequence[0]->PRCODE !== 'dummy') {
            foreach ($sequence as $item) {
                AWF_SEQUENCE_LOG::where('WCSHNA', '=', $workCenter->WCSHNA)
                    ->where('SEQUID', '=', $item->SEQUID)
                    ->whereNull('LSTIME')
                    ->whereNull('LETIME')
                    ->update([
                        'LSTIME' => (new \DateTime()),
                    ]);
            }

            $productRank = DB::select('
                select ppd.PORANK
                    from PRODUCT p
                           join PRWFDATA pfd on p.PRCODE = pfd.PRCODE
                           join PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = "' . $workCenter->WCSHNA . '"
                           join PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                    where p.PRCODE = "' . $sequence[0]->PRCODE . '"'
            );

            if (!empty($productRank[0])) {
                foreach ($sequence as $item) {
                    AWF_SEQUENCE::where('SEQUID', '=', $item->SEQUID)
                        ->first()
                        ->update([
                            'SEINPR' => $productRank[0]->PORANK,
                        ]);
                }
            }
        }

        if ($workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->WFVALU == 'success') {
            $workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
                'WFVALU' => 'default',
            ]);
        }

        publishMqtt(env('DEPLOYMENT_SUBDOMAIN') . '/api/SEQUENCE_CHANGE/', [
            [
                "to" => 'dh:' . $workCenter->operatorPanels[0]->dashboard->DHIDEN,
                "payload" => [
                    "status" => "default",
                ],
            ]
        ]);

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                true,
                (new SequenceFacadeResponse($sequence, $workCenter))->generate()
            ),
            Response::HTTP_OK
        ));
    }

    public function set(string $pillar, string $sequenceId): RedirectResponse
    {
        $sequences = AWF_SEQUENCE::where('SEPILL', '=', $pillar)
            ->where('SEQUID', '<', $sequenceId)
            ->where('SEINPR', '=', 0)
            ->update([
                'SEINPR' => 99,
            ]);

        return back()->with('notification-success', __('responses.update'));
    }
}
