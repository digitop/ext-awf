<?php

namespace AWF\Extension\Helpers\Mailer;

use AWF\Extension\Helpers\Mailer\Mailables\NotAllPillarAvailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Collection;

class Mailer
{
    public static function sendIsAllPillarAvailable(Collection $sequences): void
    {
        Mail::to('kassai.kristof@digitop.hu')->send(new NotAllPillarAvailable($sequences));
    }
}
