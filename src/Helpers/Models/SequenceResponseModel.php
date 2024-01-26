<?php

namespace AWF\Extension\Helpers\Models;

use App\Models\OPERATOR_PANEL;
use Illuminate\Database\Eloquent\Collection;

class SequenceResponseModel extends ObjectToArray
{
    protected int $SEQUID;
    protected string $SEPONR;
    protected string $SEPSEQ;
    protected string $SEARNU;
    protected string $SESIDE;
    protected string $ORCODE;
    protected string|null $OPNAME;
    protected string|null $RNREPN;
    protected string|null $DHIDEN;
    protected bool $SESCRA = false;
    protected string|null $preparatory = null;
    protected string|null $welder = null;
    protected string $color;
    protected string|null $colorDesignation = null;
    protected string $material;
    protected array $previousRepnos = [];

    public function getSEQUID(): int
    {
        return $this->SEQUID;
    }

    public function setSEQUID(int $SEQUID): SequenceResponseModel
    {
        $this->SEQUID = $SEQUID;
        return $this;
    }

    public function getSEPONR(): string
    {
        return $this->SEPONR;
    }

    public function setSEPONR(string $SEPONR): SequenceResponseModel
    {
        $this->SEPONR = $SEPONR;
        return $this;
    }

    public function getSEPSEQ(): string
    {
        return $this->SEPSEQ;
    }

    public function setSEPSEQ(string $SEPSEQ): SequenceResponseModel
    {
        $this->SEPSEQ = $SEPSEQ;
        return $this;
    }

    public function getSEARNU(): string
    {
        return $this->SEARNU;
    }

    public function setSEARNU(string $SEARNU): SequenceResponseModel
    {
        $this->SEARNU = $SEARNU;
        return $this;
    }

    public function getSESIDE(): string
    {
        return $this->SESIDE;
    }

    public function setSESIDE(string $SESIDE): SequenceResponseModel
    {
        $this->SESIDE = $SESIDE;
        return $this;
    }

    public function getORCODE(): string
    {
        return $this->ORCODE;
    }

    public function setORCODE(string $ORCODE): SequenceResponseModel
    {
        $this->ORCODE = $ORCODE;
        return $this;
    }

    public function getOPNAME(): null|string
    {
        return $this->OPNAME;
    }

    public function setOPNAME(OPERATOR_PANEL|null $operatorPanel): SequenceResponseModel
    {
        if ($operatorPanel !== null) {
            $this->OPNAME = $operatorPanel->OPNAME;

            $this->setDHIDEN($operatorPanel->dashboard->DHIDEN);
        }

        return $this;
    }

    public function getRNREPN(): string|null
    {
        return $this->RNREPN;
    }

    public function setRNREPN(string|null $RNREPN): SequenceResponseModel
    {
        $this->RNREPN = $RNREPN;
        return $this;
    }

    public function getDHIDEN(): string|null
    {
        return $this->DHIDEN;
    }

    public function setDHIDEN(string|null $DHIDEN): SequenceResponseModel
    {
        $this->DHIDEN = $DHIDEN;
        return $this;
    }

    public function getPreparatory(): string|null
    {
        return $this->preparatory;
    }

    public function setPreparatory(string|null $preparatory): SequenceResponseModel
    {
        $this->preparatory = $preparatory;
        return $this;
    }

    public function getWelder(): string|null
    {
        return $this->welder;
    }

    public function setWelder(string|null $welder): SequenceResponseModel
    {
        $this->welder = $welder;
        return $this;
    }

    public function isSESCRA(): bool
    {
        return $this->SESCRA;
    }

    public function setSESCRA(bool|null $SESCRA = false): SequenceResponseModel
    {
        $this->SESCRA = $SESCRA;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): SequenceResponseModel
    {
        $this->color = $color;
        return $this;
    }

    public function getColorDesignation(): ?string
    {
        return $this->colorDesignation;
    }

    public function setColorDesignation(?string $colorDesignation): SequenceResponseModel
    {
        $this->colorDesignation = $colorDesignation;
        return $this;
    }

    public function getMaterial(): string
    {
        return $this->material;
    }

    public function setMaterial(string $material): SequenceResponseModel
    {
        $this->material = $material;
        return $this;
    }

    public function getPreviousRepnos(): array
    {
        return $this->previousRepnos;
    }

    public function setPreviousRepnos(Collection $previousRepnos): SequenceResponseModel
    {
        if (!empty($previousRepnos) && !empty($previousRepnos[0])) {
            foreach ($previousRepnos as $previousRepno) {
                $this->previousRepnos[] = [
                    'ORCODE' => $previousRepno->ORCODE,
                    'RNREPN' => $previousRepno->RNREPN,
                    'RNOLMU' => 1,
                    'type' => 'unassigned',
                ];
            }
        }
        return $this;
    }
}
