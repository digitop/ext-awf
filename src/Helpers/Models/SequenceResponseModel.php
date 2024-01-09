<?php

namespace AWF\Extension\Helpers\Models;

class SequenceResponseModel extends ObjectToArray
{
    protected int $SEQUID;
    protected string $SEPONR;
    protected string $SEPSEQ;
    protected string $SEARNU;
    protected string $SESIDE;
    protected string $preparatory;
    protected string $welder;
    protected string $color;
    protected string $material;

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

    public function getPreparatory(): string
    {
        return $this->preparatory;
    }

    public function setPreparatory(string $preparatory): SequenceResponseModel
    {
        $this->preparatory = $preparatory;
        return $this;
    }

    public function getWelder(): string
    {
        return $this->welder;
    }

    public function setWelder(string $welder): SequenceResponseModel
    {
        $this->welder = $welder;
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

    public function getMaterial(): string
    {
        return $this->material;
    }

    public function setMaterial(string $material): SequenceResponseModel
    {
        $this->material = $material;
        return $this;
    }
}
