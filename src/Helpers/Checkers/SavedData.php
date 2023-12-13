<?php

namespace AWF\Extension\Helpers\Checkers;

use AWF\Extension\Helpers\Mailer\Mailer;
use AWF\Extension\Models\AWF_SEQUENCE;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SavedData
{
    public static function check(): void
    {
        $sequences = AWF_SEQUENCE::where([
            ['WCSHNA', '=', null],
            ['SEINPR', '=', 0]
        ])->get();

        if (!self::isAllPillarAvailable($sequences)) {
            Mailer::sendIsAllPillarAvailable(
                DB::connection('custom_mysql')
                    ->table('AWF_SEQUENCE')
                    ->selectRaw('SEPONR, SEPSEQ')
                    ->groupBy('SEPONR', 'SEPSEQ')
                    ->havingRaw('count(SEPONR) < 6')
                    ->get()
            );
        }
    }

    protected static function isAllPillarAvailable(Collection $sequences): bool
    {
        foreach ($sequences as $sequence) {
            $pillarCount =
                DB::connection('custom_mysql')
                    ->select('select count(SEPONR) as countedPiece from AWF_SEQUENCE where SEPONR=?', [$sequence->SEPONR]);

            if ($pillarCount[0]['countedPiece'] !== 6) {
                return false;
            }
        }

        return true;
    }
}
