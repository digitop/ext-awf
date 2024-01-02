<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\PRODUCT;

class SequenceFacadeResponse
{
    protected Collection|Model $sequences;
    protected Model|null $workCenter;

    public function __construct(Collection|Model $sequences, Model|null $workCenter)
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
        $product = PRODUCT::where('PRCODE', '=', $sequence->PRCODE)->first();

        if (!empty($sequenceWorkCenter) || $this->workCenter === null) {
            return [
                'SEPONR' => $sequence->SEPONR,
                'SEPSEQ' => $sequence->SEPSEQ,
                'SEARNU' => $sequence->SEARNU,
                'SESIDE' => $sequence->SESIDE,
                'TEKE' => $product?->features()->where('FESHNA', '=', 'TEKE')->first()?->FEBLOB,
                'SZASZ' => $product?->features()->where('FESHNA', '=', 'TEKE')->first()?->FEVALU,
            ];
        }

        return [];
    }
}
