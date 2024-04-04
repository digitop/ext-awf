<?php

namespace AWF\Extension\Helpers\Facades\Controllers\Api;

use AWF\Extension\Events\NextProductEvent;
use AWF\Extension\Events\WelderNextProductEvent;
use AWF\Extension\Helpers\Facades\Controllers\Web\WelderPanelFacade;
use AWF\Extension\Helpers\Responses\JsonResponseModel;
use AWF\Extension\Helpers\Responses\ResponseData;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
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
use App\Models\SERIALNUMBER;

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
        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';
        $database = config('database.connections.mysql.database');

        if (is_string($pillar) && $pillar === 'null') {
            $pillar = null;
        }

        $logs = DB::connection('custom_mysql')->select('
            select * from AWF_SEQUENCE_LOG where LETIME is null and (LSTIME is null or LSTIME > "' . $start .
            '") and WCSHNA = "' . $workCenter->WCSHNA . '"
            order by SEQUID
        ');

        if ($logs === null || !array_key_exists(0, $logs) || empty($logs[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.no_new_data_available')
                ),
                Response::HTTP_OK
            ));
        }

        $queryString = '
            select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR, a.SESCRA, r.RNREPN
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA ' .
                (
                    $request->has('serial') && !empty($request->serial) ?
                        'join ' . $database .
                            '.SERIALNUMBER s on r.ORCODE = substring(s.RNREPN, 1, position("-" in s.RNREPN) - 1) and
                             s.SNSERN = "' . $request->serial . '" ' :
                        ''
                ) .
            'where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
            '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
                ($pillar !== null ? ' and a.SEPILL = "' . $pillar .'"' : '') .
                ($request->has('side') ? ' and a.SESIDE = "' . $request->side . '"' : '') .
                ' order by a.SEQUID' .
            ($request->has('limit') ? ' limit ' . $request->limit : '');

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
                    join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
                where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
                    '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and a.SEINPR <= r.PORANK and
                    asl.WCSHNA = "' . $workCenter->WCSHNA . '" and a.SESIDE = "' . $side . '"' .
                    ($pillar !== null ? ' and a.SEPILL = "' . $pillar .'"' : '') .
                ' order by a.SEQUID limit 1'
            ;

            $nextIsScrapSequence = DB::connection('custom_mysql')->select($queryString);

            if (
                array_key_exists(0, $nextIsScrapSequence) &&
                is_object($nextIsScrapSequence[0]) &&
                $nextIsScrapSequence[0]->SESCRA == true
            ) {
                $sequence = new Collection($nextIsScrapSequence);
            }
        }

        if (empty($sequence[0])) {
            return new CustomJsonResponse(new JsonResponseModel(
                new ResponseData(
                    false,
                    [],
                    __('response.no_new_data_available')
                ),
                Response::HTTP_OK
            )
            );
        }

        $noChange = $request->has('no_change') &&
            (
                (is_string($request->no_change) && $request->no_change == 'true') ||
                (is_bool($request->no_change) && $request->no_change == true)
            );

        $toPreparationPanel = $request->has('to_preparation_panel') &&
            (
                (is_string($request->to_preparation_panel) && $request->to_preparation_panel == 'true') ||
                (is_bool($request->to_preparation_panel) && $request->to_preparation_panel == true)
            );

        if ($noChange && $workCenter->WCSHNA === 'EL01' && $toPreparationPanel) {
            event(new NextProductEvent(
                    (new NextProductEventResponse($sequence, null))->generate()
                )
            );
        }

        if (!$noChange) {
            $queryString = '
                select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR, a.SESCRA, r.RNREPN
                from AWF_SEQUENCE_LOG asl
                    join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                    join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                    join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA ' .
                'where ((asl.LSTIME is null and a.SEINPR = (r.PORANK - 1)) or (asl.LSTIME > "' . $start .
                '" and a.SEINPR = r.PORANK)) and asl.LETIME is null and
                    asl.WCSHNA = "' . $workCenter->WCSHNA . '"' .
                ($pillar !== null ? ' and a.SEPILL = "' . $pillar . '"' : '') .
                ($request->has('side') ? ' and a.SESIDE = "' . $request->side . '"' : '') .
                ' order by a.SEQUID' .
                ($request->has('limit') ? ' limit ' . $request->limit : '');

            $nextSequence = DB::connection('custom_mysql')->select($queryString);

            if (array_key_exists(0, $nextSequence) && !empty($nextSequence[0])) {
                $nextSequence = $nextSequence[0];

                if ($sequence[0]->SEQUID !== $nextSequence->SEQUID) {
                    $statement = DB::connection('custom_mysql')->select("show table status like 'AWF_SEQUENCE'");

                    AWF_SEQUENCE::where('SEQUID', '=', $nextSequence->SEQUID)->update([
                        'SEQUID' => $statement[0]->Auto_increment,
                    ]);

                    AWF_SEQUENCE::where('SEQUID', '=', $sequence[0]->SEQUID)->update([
                        'SEQUID' => $nextSequence->SEQUID,
                    ]);

                    AWF_SEQUENCE::where('SEQUID', '=', $statement[0]->Auto_increment)->update([
                        'SEQUID' => $sequence[0]->SEQUID,
                    ]);

                    $sequence[0]->SEQUID = $nextSequence->SEQUID;
                }
            }

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
            where a.SEINPR > 0 and (asl.LSTIME is not null or asl.LETIME is null) and a.SEQUID < ' . $sequenceId . ' 
            and a.SEPILL = "' . $pillar . '"'
        );

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

        $logs = DB::connection('custom_mysql')->select('
            select asl.WCSHNA, a.SEQUID from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID
            where a.SEINPR >= 0 and a.SEQUID >= ' . $sequenceId . ' and a.SEPILL = "' . $pillar . '"'
        );

        if (array_key_exists(0, $logs) && !empty($logs[0])) {
            foreach ($logs as $log) {
                AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)->update(['SEINPR' => 0]);

                if ($log->WCSHNA === 'EL01') {
                    AWF_SEQUENCE_LOG::where('SEQUID', '=', $log->SEQUID)
                        ->where('WCSHNA', '=', $log->WCSHNA)
                        ->update([
                            'LSTIME' => null,
                            'LETIME' => null,
                        ]);
                }
                else {
                    AWF_SEQUENCE_LOG::where('SEQUID', '=', $log->SEQUID)
                        ->where('WCSHNA', '=', $log->WCSHNA)
                        ->delete();
                }
            }
        }

        return back()->with('notification-success', __('responses.update'));
    }

    public function setWorkCenter(Model $workCenter, string $sequenceId): RedirectResponse
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $database = config('database.connections.mysql.database');

        $queryString = '
            select a.PRCODE, a.SEQUID, a.SEPSEQ, a.SEARNU, a.ORCODE, a.SESIDE, a.SEPILL, a.SEPONR, a.SEINPR, a.SESCRA
            from AWF_SEQUENCE_LOG asl
                join AWF_SEQUENCE a on a.SEQUID = asl.SEQUID
                join ' . $database . '.PRODUCT p on p.PRCODE = a.PRCODE
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.WCSHNA = asl.WCSHNA
            where a.SEQUID = ' . $sequenceId
        ;

        $sequence = DB::connection('custom_mysql')->select($queryString);

        if (!array_key_exists(0, $sequence) || empty($sequence[0])) {
            return back();
        }

        $sequence = $sequence[0];

        $rank = DB::select(
            'select PORANK from REPNO where ORCODE = "' . $sequence->ORCODE . '" and WCSHNA = "' .
                $workCenter->WCSHNA . '"'
        );

        if (!array_key_exists(0, $rank) || empty($rank[0])) {
            return back();
        }

        $rank = $rank[0];

        $maxRank = DB::select(
            'select max(PORANK) as maxRank from REPNO where ORCODE = "' . $sequence->ORCODE . '"'
        );

        if (!array_key_exists(0, $maxRank) || empty($maxRank[0])) {
            return back();
        }

        $maxRank = $maxRank[0];

        $data = AWF_SEQUENCE::selectRaw('AWF_SEQUENCE.*')
            ->join('AWF_SEQUENCE_LOG as asl', function ($join) {
                $join->on('asl.SEQUID', '=', 'AWF_SEQUENCE.SEQUID');
            })
            ->where(function ($query) use ($workCenter) {
                $query->where('asl.WCSHNA', '=', $workCenter->WCSHNA);
            })
            ->first();

        $logs = DB::connection('custom_mysql')->select('
            select a.SEQUID from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID
            where a.SEINPR >= 0 and a.SEQUID < ' . $sequenceId .
                ' and a.SESIDE = "' . $data->SESIDE . '" and a.SEPILL = "' . $data->SEPILL .
                '" and a.SEINPR < ' . $maxRank->maxRank
        );

        if (array_key_exists(0, $logs) && !empty($logs[0])) {
            foreach ($logs as $log) {
                AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)
                    ->where('SEINPR', '<', $maxRank->maxRank)
                    ->update([
                        'SEINPR' => $maxRank->maxRank,
                    ]);

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

        $logs = DB::connection('custom_mysql')->select('
            select r.WCSHNA, a.SEQUID, a.SEINPR, r.PORANK, r.RNREPN from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID
                join ' . $database . '.REPNO r on r.ORCODE = a.ORCODE and r.PORANK <= ' . $rank->PORANK . '
            where a.SEINPR >= 0 and a.SEQUID = ' . $sequenceId .
            ' and a.SESIDE = "' . $data->SESIDE . '" and a.SEPILL = "' . $data->SEPILL . '"'
        );

        if (array_key_exists(0, $logs) && !empty($logs[0])) {
            foreach ($logs as $log) {
                AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)->update(['SEINPR' => $log->PORANK]);

                if (
                    !empty(
                        AWF_SEQUENCE_LOG::where('SEQUID', '=', $log->SEQUID)
                            ->where('WCSHNA', '=', $log->WCSHNA)
                            ->first()
                    )
                ) {
                    AWF_SEQUENCE_LOG::where('SEQUID', '=', $log->SEQUID)
                        ->where('WCSHNA', '=', $log->WCSHNA)
                        ->update([
                            'LSTIME' => $now,
                            'LETIME' => $now,
                        ]);
                }
                else {
                    AWF_SEQUENCE_LOG::create([
                        'SEQUID' => $log->SEQUID,
                        'WCSHNA' => $log->WCSHNA,
                        'LSTIME' => $now,
                        'LETIME' => null,
                    ]);
                }

                if (
                    empty(
                        AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $log->SEQUID)
                            ->where('WCSHNA', '=', $log->WCSHNA)
                            ->first()
                    )
                ) {
                    AWF_SEQUENCE_WORKCENTER::create([
                        'SEQUID' => $log->SEQUID,
                        'WCSHNA' => $log->WCSHNA,
                        'RNREPN' => $log->RNREPN,
                    ]);
                }
            }
        }

        $logs = DB::connection('custom_mysql')->select('
            select asl.WCSHNA, a.SEQUID, a.SEINPR from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID
            where a.SEINPR >= 0 and a.SEQUID > ' . $sequenceId .
                ' and a.SESIDE = "' . $data->SESIDE . '" and a.SEPILL = "' . $data->SEPILL . '"'
        );

        if (array_key_exists(0, $logs) && !empty($logs[0])) {
            foreach ($logs as $log) {
                AWF_SEQUENCE::where('SEQUID', '=', $log->SEQUID)->update(['SEINPR' => 0]);

                if ($log->WCSHNA === 'EL01') {
                    AWF_SEQUENCE_LOG::where('SEQUID', '=', $log->SEQUID)
                        ->where('WCSHNA', '=', $log->WCSHNA)
                        ->update([
                            'LSTIME' => null,
                            'LETIME' => null,
                        ]);
                }
                else {
                    AWF_SEQUENCE_LOG::where('SEQUID', '=', $log->SEQUID)
                        ->where('WCSHNA', '=', $log->WCSHNA)
                        ->delete();
                }
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

        $startShift = array_key_exists('startShift', $request->all()) &&
            (
                (is_string($request->startShift) && $request->startShift === 'true') ||
                (is_bool($request->startShift) && $request->startShift === true)
            );

        $database = config('database.connections.mysql.database');

        $start = (new \DateTime())->format('Y-m-d') . ' 00:00:00';

        $waitings = DB::connection('custom_mysql')->select('
            select asl.LSTIME, r.RNREPN, s.SNSERN, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL,
                   a.SEINPR, a.PRCODE, a.ORCODE, ppd.PFIDEN, ppd.PORANK, ppd.OPSHNA
            from AWF_SEQUENCE a
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = "' . $workCenter->WCSHNA . '"
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                join ' . $database . '.REPNO r on r.WCSHNA = pcd.WCSHNA and r.ORCODE = a.ORCODE
                left join ' . $database . '.SERIALNUMBER s on s.RNREPN = r.RNREPN and s.PRCODE = a.PRCODE
                left join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = pcd.WCSHNA
            where a.SEINPR < ppd.PORANK and a.SESIDE = "L"
                and (asl.LSTIME >= "' . $start . '" or asl.LSTIME is null)
            order by asl.LSTIME DESC, a.SEQUID limit 2
        ');

        $alreadyReaded = false;

        $queryString = '
            select r.RNREPN, s.SNSERN, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL
            from AWF_SEQUENCE a
                join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID and asl.WCSHNA = "' . $workCenter->WCSHNA . '"
                join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                join ' . $database . '.REPNO r on r.WCSHNA = pcd.WCSHNA and r.ORCODE = a.ORCODE
                left join ' . $database . '.SERIALNUMBER s on s.RNREPN = r.RNREPN and s.PRCODE = a.PRCODE
            where a.SEINPR <= ppd.PORANK
                and (asl.LSTIME >= "' . $start . '" or asl.LSTIME is null) and asl.LETIME is null
            order by asl.LSTIME DESC, a.SEQUID
        ';

        $inProgress = DB::connection('custom_mysql')->select($queryString);

        if (array_key_exists(0, $inProgress) && !empty($inProgress)) {
            $inProgress = $inProgress[0];
        }

        if (is_object($inProgress) && !empty($inProgress?->SNSERN)) {
            $side = $inProgress->SESIDE === 'L' ? 'R' : 'L';

            $queryString = '
                select r.RNREPN, s.SNSERN, a.SEQUID, a.SEPONR, a.SEPSEQ, a.SESIDE, a.SEPILL
                from AWF_SEQUENCE a
                    join AWF_SEQUENCE_LOG asl on a.SEQUID = asl.SEQUID
                    join ' . $database . '.PRWFDATA pfd on pfd.PRCODE = a.PRCODE
                    join ' . $database . '.PRWCDATA pcd on pfd.PFIDEN = pcd.PFIDEN and pcd.WCSHNA = asl.WCSHNA
                    join ' . $database . '.PROPDATA ppd on ppd.PFIDEN = pcd.PFIDEN and ppd.OPSHNA = pcd.OPSHNA
                    join ' . $database . '.REPNO r on r.WCSHNA = pcd.WCSHNA and r.ORCODE = a.ORCODE
                    left join ' . $database . '.SERIALNUMBER s on s.RNREPN = r.RNREPN and s.PRCODE = a.PRCODE
                where a.SEINPR <= ppd.PORANK
                    and (asl.LSTIME >= "' . $start . '" or asl.LSTIME is null) and asl.LETIME is not null 
                    and a.SESIDE = "' . $side . '" and a.SEPONR = "' . $inProgress->SEPONR . '" 
                    and a.SEPSEQ = "' . $inProgress->SEPSEQ . '" and a.SEPILL = "' . $inProgress->SEPILL . '"
                order by asl.LSTIME DESC, a.SEQUID
            ';

            $inProgressOtherSide = DB::connection('custom_mysql')->select($queryString);

            if (array_key_exists(0, $inProgressOtherSide) && !empty($inProgressOtherSide)) {
                $inProgressOtherSide = $inProgressOtherSide[0];
            }

            if (is_object($inProgressOtherSide) && !empty($inProgressOtherSide?->SNSERN)) {
                $alreadyReaded = true;
            }
        }

        if (array_key_exists(0, $waitings) && !empty($waitings[0])) {
            $sequence2 = new Collection([$waitings[0]]);
        }

        if (array_key_exists(1, $waitings) && !empty($waitings[1])) {
            $sequence3 = new Collection([$waitings[1]]);
        }

        if ($workCenter->WCSHNA == 'HCB01' && !empty($sequence2)) {
            $sequence3 = $sequence2;
        }

        if (!empty($sequence2) && !empty($sequence3) && ($alreadyReaded || $startShift)) {
            event(new WelderNextProductEvent(
                (new WelderNextProductEventResponse(
                    $sequence2 ?? null,
                    $workCenter
                ))
                    ->setNext($sequence3 ?? null)
                    ->setStartShift($startShift)
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
