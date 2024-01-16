<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Helpers\Models\SequenceResponseModel;
use AWF\Extension\Helpers\ProductFeaturesImagesUrl;
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
                $data[$sequence->SEPILL][] = $this->make($sequence)->get();
            }
        }

        if ($this->sequences instanceof AWF_SEQUENCE) {
            $data[$this->sequences->SEPILL][] = $this->make($this->sequences)->get();
        }

        return $data;
    }

    protected function setWorkCenter(Model|null $workCenter, Model|null $sequence): void
    {
        if ($workCenter !== null) {
            $this->workCenter = $workCenter;
        }
    }

    protected function make(Model $sequence): SequenceResponseModel
    {
        $this->setWorkCenter($this->workCenter, $sequence);

        $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequence->SEQUID)
            ->where('WCSHNA', '=', $this->workCenter?->WCSHNA)
            ->first();

        $product = PRODUCT::where('PRCODE', '=', $sequence->PRCODE)->first();

        return (new SequenceResponseModel())
            ->setSEQUID($sequence->SEQUID)
            ->setSEPONR($sequence->SEPONR)
            ->setSEPSEQ($sequence->SEPSEQ)
            ->setSEARNU($sequence->SEARNU)
            ->setSESIDE($sequence->SESIDE)
            ->setORCODE($sequence->ORCODE)
            ->setOPNAME(
                isset($this->workCenter?->operatorPanels) &&
                !empty($this->workCenter?->operatorPanels[0]) ?
                    $this->workCenter?->operatorPanels[0] :
                    null
            )
            ->setRNREPN($sequenceWorkCenter?->RNREPN)
            ->setPreparatory(ProductFeaturesImagesUrl::getUrl(
                $product?->features()->where('FESHNA', '=', 'TEKEEL')->first()?->FEVALU
            ))
            ->setWelder(ProductFeaturesImagesUrl::getUrl(
                $product?->features()->where('FESHNA', '=', 'TEKEHE')->first()?->FEVALU
            ))
            ->setColor($product?->features()->where('FESHNA', '=', 'SZASZ')->first()?->FEVALU)
            ->setMaterial($product?->features()->where('FESHNA', '=', 'SZAA')->first()?->FEVALU);
    }
}
