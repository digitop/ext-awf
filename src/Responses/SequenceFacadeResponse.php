<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SequenceFacadeResponse
{
    protected Collection|Model $sequences;
    protected Model $workCenter;

    public function __construct(Collection|Model $sequences, Model $workCenter)
    {
        $this->sequences = $sequences;
        $this->workCenter = $workCenter;
    }

    public function generate(): array
    {
        $data = [];

        if ($this->sequences instanceof Collection) {
            foreach ($this->sequences as $sequence) {
                $data[$sequence->SEPILL][] = $this->make($sequence);
            }
        }

        if ($this->sequences instanceof AWF_SEQUENCE) {
            $data[$this->sequences->SEPILL][] = $this->make($this->sequences);
        }

        return $data;
    }

    protected function make(Model $sequence): array
    {
        $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequence->SEQUID)
            ->where('WCSHNA', '=', $this->workCenter->WCSHNA)
            ->first();

        if (!empty($sequenceWorkCenter)) {
            return [
                'SEPONR' => $sequence->SEPONR,
                'SEPSEQ' => $sequence->SEPSEQ,
                'SEARNU' => $sequence->SEARNU,
                'SESIDE' => $sequence->SESIDE,
            ];
        }

        return [];
    }
}
