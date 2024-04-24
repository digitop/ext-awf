<?php

namespace AWF\Extension\Helpers\Mailer;

use AWF\Extension\Helpers\Mailer\Mailables\NotAllPillarAvailable;
use AWF\Extension\Helpers\Mailer\Mailables\NotBothSideAvailable;
use Illuminate\Support\Facades\Mail;
use  Illuminate\Support\Collection;

class Mailer
{
    public static function sendIsAllPillarAvailable(Collection $sequences): void
    {
        Mail::to('kassai.kristof@digitop.hu')->send(new NotAllPillarAvailable($sequences));
    }

    public static function sendIsBothSideAvailable(Collection $sequences): void
    {
        Mail::to('kassai.kristof@digitop.hu')->send(new NotBothSideAvailable($sequences));
    }

    public static function sendSeqOccursSeveral(Collection $sequencesPillar, Collection $sequencesSide): void
    {
        Mail::to('kassai.kristof@digitop.hu')->send(new SeqOccursSeveral($sequencesPillar, $sequencesSide));
    }
}
