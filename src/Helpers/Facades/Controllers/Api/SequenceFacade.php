<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Events\NextProductEvent;
use AWF\Extension\Events\WelderNextProductEvent;
use AWF\Extension\Helpers\Facades\Controllers\Web\PreparationStationPanelFacade;
use AWF\Extension\Helpers\Facades\Controllers\Web\WelderPanelFacade;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Responses\CustomJsonResponse;
use AWF\Extension\Responses\NextProductEventResponse;
use AWF\Extension\Responses\SequenceFacadeResponse;
use AWF\Extension\Responses\WelderNextProductEventResponse;
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

        $database = config('database.connections.mysql.database');

        $queryString = '
            select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR, a.SESCRA
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
            where asl.LSTIME is null and asl.LETIME is null and a.SEINPR = (ppd.PORANK - 1) and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
                ($pillar !== null ? ' and a.SEPILL = "' . $pillar .'"' : '') .
                ($request->has('side') ? ' and a.SESIDE = "' . $request->side . '"' : '') .
                ' order by a.SEQUID' .
                ($request->has('limit') ? ' limit ' . $request->limit : '')
        ;

        $sequence = new Collection(DB::connection('custom_mysql')->select($queryString));

        if (!empty($sequence[0])) {
            $side = $request->has('side') ? $request->side : null;

            if (empty($side)) {
                $side = $sequence[0]->SESIDE;
            }

            if ($side == 'L') {
                $side = 'R';
            }
            elseif ($side == 'R') {
                $side = 'L';
            }

            $queryString = '
            select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR, a.SESCRA
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
            where asl.LSTIME is null and asl.LETIME is null and a.SEINPR <= ppd.PORANK and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '" and a.SESIDE = "' . $side . '"' .
                ($pillar !== null ? ' and a.SEPILL = "' . $pillar .'"' : '') .
                ' order by a.SEQUID limit 1'
            ;

            $nextIsScrapSequence = DB::connection('custom_mysql')->select($queryString);

            if (!empty($nextIsScrapSequence[0])) {
                $nextIsScrapSequence = $nextIsScrapSequence[0];
            }

            if (is_object($nextIsScrapSequence) && $nextIsScrapSequence?->SESCRA == true) {
                $sequence = null;
            }
        }

        if (empty($sequence[0])) {
            $sequence = new Collection([(object)
                [
                    'SEQUID' => 0,
                    'PRCODE' => 'dummy',
                    'ORCODE' => 'dummy',
                    'SESIDE' => $request->side,
                    'SEPILL' => $pillar,
                    'SESCRA' => false,
                    'SEPONR' => null,
                ]
            ]);
        }

        $noChange = $request->has('no_change') &&
            (
                (is_string($request->no_change) && $request->no_change == 'true') ||
                (is_bool($request->no_change) && $request->no_change == true)
            );

        if ($noChange && $workCenter->WCSHNA === 'EL01' && $sequence[0]->PRCODE !== 'dummy') {
            event(new NextProductEvent(
                    (new NextProductEventResponse($sequence, null))->generate()
                )
            );
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
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        AWF_SEQUENCE::where('SEPILL', '=', $pillar)
            ->where('SEQUID', '<', $sequenceId)
            ->where('SEINPR', '<', 5)
            ->update([
                'SEINPR' => 99,
            ]);

        $logs = DB::connection('custom_mysql')->select('
            select a.SEQUID from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID
            where a.SEINPR > 0 and (asl.LSTIME is null or asl.LETIME is null) and a.SEQUID < 111479
        ');

        if (array_key_exists(0, $logs) && !empty($logs[0])) {
            foreach ($logs as $log) {
                AWF_SEQUENCE_LOG::where('SEQUID', '=', $log->SEQUID)
                    ->whereNull('LSTIME')
                    ->update([
                        'LSTIME' => $now,
                    ]);

                AWF_SEQUENCE_LOG::where('SEQUID', '=', $log->SEQUID)
                    ->whereNull('LETIME')
                    ->update([
                        'LETIME' => $now,
                    ]);
            }
        }

        return back()->with('notification-success', __('responses.update'));
    }

    public function welder(Request|null $request = null, Model|string|null ...$model): JsonResponse
    {
        list($pillar, $workCenter) = $model;

        if (is_string($pillar) && $pillar === 'null') {
            $pillar = null;
        }

        $database = config('database.connections.mysql.database');

        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

        $waitings = DB::connection('custom_mysql')->select('
            select asl.LSTIME, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL, a.SEINPR, a.PRCODE, a.ORCODE, ppd.PFIDEN, ppd.PORANK, ppd.OPSHNA
            from AWF_SEQUENCE a
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = "' . $workCenter->WCSHNA . '"
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                left join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = pcd.WCSHNA
            where a.SEINPR < ppd.PORANK and a.SESIDE = "L"
                and (asl.LSTIME >= "' . $start . '" or asl.LSTIME is null)
            order by asl.LSTIME DESC, a.SEQUID limit 2
        ');

        if (array_key_exists(0, $waitings) && !empty($waitings[0])) {
            $sequence2 = new Collection([$waitings[0]]);
        }

        if (array_key_exists(1, $waitings) && !empty($waitings[1])) {
            $sequence3 = new Collection([$waitings[1]]);
        }

        if (!empty($sequence2) && !empty($sequence3)) {
            event(new WelderNextProductEvent(
                (new WelderNextProductEventResponse(
                    $sequence2 ?? null,
                    $workCenter
                ))
                    ->setNext($sequence3 ?? null)
                    ->generate()
            ));

            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    true
                ),
                Response::HTTP_OK
            ));
        }

        (new WelderPanelFacade())->default($workCenter);

        return new CustomJsonResponse(new JsonResponseModel(
            new ResponseData(
                false
            ),
            Response::HTTP_OK
        ));
    }
}
