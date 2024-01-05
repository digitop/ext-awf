<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_LOG;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\PRODUCT;
use App\Models\WORKCENTER;

class SequenceFacadeResponse
{
    protected Collection|Model $sequences;
    protected Model|null $workCenter = null;

    public function __construct(Collection|Model $sequences, Model|null $workCenter)
    {
        $this->sequences = $sequences;
        $this->setWorkCenter($workCenter, ($sequences instanceof AWF_SEQUENCE) ? $sequences : null);
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

    protected function setWorkCenter(Model|null $workCenter, Model|null $sequence): void
    {
        if ($this->sequences instanceof AWF_SEQUENCE || $sequence !== null) {
            if ($sequence === null) {
                $sequence = $this->sequences;
            }

            $workCenterId = AWF_SEQUENCE_LOG::where('SEQUID', '=', $sequence->SEQUID)
                ->whereNull('LETIME')
                ->first()?->WCSHNA;

            if (!empty($workCenterId)) {
                $this->workCenter = WORKCENTER::where('WCSHNA', '=', $workCenterId)->first();
            }
        }

        if ($workCenter !== null) {
            $this->workCenter = $workCenter;
        }
    }

    protected function make(Model $sequence): array
    {
        $this->setWorkCenter($this->workCenter, $sequence);

        $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequence->SEQUID)
            ->where('WCSHNA', '=', $this->workCenter->WCSHNA)
            ->first();

        $product = PRODUCT::where('PRCODE', '=', $sequence->PRCODE)->first();

        if (!empty($sequenceWorkCenter) || $this->workCenter === null) {
            $EL_image = $product?->features()->where('FESHNA', '=', 'TEKEEL')->first()?->FEVALU;
            $HE_image = $product?->features()->where('FESHNA', '=', 'TEKEHE')->first()?->FEVALU;

            if ($EL_image !== null) {
                $EL_image = base64_encode(\Illuminate\Support\Facades\Storage::disk('products')->get($EL_image));
            }

            if ($HE_image !== null) {
                $HE_image = base64_encode(\Illuminate\Support\Facades\Storage::disk('products')->get($HE_image));
            }

            return [
                'SEPONR' => $sequence->SEPONR,
                'SEPSEQ' => $sequence->SEPSEQ,
                'SEARNU' => $sequence->SEARNU,
                'SESIDE' => $sequence->SESIDE,
                'EL_image' => $EL_image,
                'HE_image' => $HE_image,
                'SZASZ' => $product?->features()->where('FESHNA', '=', 'SZASZ')->first()?->FEVALU,
                'SZAA' => $product?->features()->where('FESHNA', '=', 'SZAA')->first()?->FEVALU,
            ];
        }

        return [];
    }
}
