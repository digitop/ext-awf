<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Helpers\Models\SequenceResponseModel;
use AWF\Extension\Helpers\ProductFeaturesImagesUrl;
use AWF\Extension\Interfaces\ResponseInterface;
use AWF\Extension\Models\AWF_SEQUENCE;
use AWF\Extension\Models\AWF_SEQUENCE_WORKCENTER;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\PRODUCT;
use App\Models\WORKCENTER;
use App\Models\REPNO;

class SequenceFacadeResponse  implements ResponseInterface
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

    public function getCollection(): Collection|Model
    {
        return $this->sequences;
    }
    public function setCollection(Collection|Model $collection): ResponseInterface
    {
        $this->sequences = $collection;
        return $this;
    }
    public function getModel(): Model|null
    {
        return $this->workCenter;
    }
    public function setModel(Model|null $model): ResponseInterface
    {
        $this->workCenter = $model;
        return $this;
    }

    protected function setWorkCenter(Model|null $workCenter, Model|\stdClass|null $sequence): void
    {
        if ($workCenter !== null) {
            $this->workCenter = $workCenter;
        }
    }

    protected function make(Model|\stdClass $sequence): SequenceResponseModel
    {
        $this->setWorkCenter($this->workCenter, $sequence);

        $sequenceWorkCenter = AWF_SEQUENCE_WORKCENTER::where('SEQUID', '=', $sequence->SEQUID)
            ->where('WCSHNA', '=', $this->workCenter?->WCSHNA)
            ->first();

        $product = PRODUCT::where('PRCODE', '=', $sequence->PRCODE)->with('features')->first();

        return (new SequenceResponseModel())
            ->setSEQUID($sequence->SEQUID)
            ->setORCODE($sequence->ORCODE)
            ->setSESIDE($sequence->SESIDE)
            ->setSEPONR($sequence->SEPONR)
            ->setOPNAME(
                isset($this->workCenter?->operatorPanels) &&
                !empty($this->workCenter?->operatorPanels[0]) ?
                    $this->workCenter?->operatorPanels[0] :
                    null
            )
            ->setRNREPN($sequenceWorkCenter?->RNREPN)
            ->setPlc($product?->features()->where('FESHNA', '=', 'PLCCOLOR')->first()?->FEVALU ?? null)
            ->setPreviousRepnos(
                REPNO::where('WCSHNA', '=', $this->workCenter?->WCSHNA)->where('RNOLAC', '=', 1)->get()
            );
    }
}
