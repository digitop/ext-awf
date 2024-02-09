<?php

namespace AWF\Extension\Helpers\Models;

class NextProductEventModel extends ObjectToArray
{
    protected string|null $designation = null;
    protected string|null $color = null;
    protected string|null $materialAndColor = null;
    protected string|null $pillar = null;
    protected string|null $side = null;
    protected string|null $porscheOrderNumber = null;
    protected string|null $porscheSequenceNumber = null;
    protected string|null $articleNumber = null;

    public function __construct(
        string|null $designation = null,
            string|null $color = null,
            string|null $materialAndColor = null
    )
    {
        $this->designation = $designation;
        $this->color = $color;
        $this->materialAndColor = $materialAndColor;
    }

    public function getDesignation(): string|null
    {
        return $this->designation;
    }

    public function setDesignation(string|null $designation): NextProductEventModel
    {
        $this->designation = $designation;
        return $this;
    }

    public function getColor(): string|null
    {
        return $this->color;
    }

    public function setColor(string|null $color): NextProductEventModel
    {
        $this->color = $color;
        return $this;
    }

    public function getMaterialAndColor(): string|null
    {
        return $this->materialAndColor;
    }

    public function setMaterialAndColor(string|null $materialAndColor): NextProductEventModel
    {
        $this->materialAndColor = $materialAndColor;
        return $this;
    }

    public function getPillar(): ?string
    {
        return $this->pillar;
    }

    public function setPillar(?string $pillar): NextProductEventModel
    {
        $this->pillar = $pillar;
        return $this;
    }

    public function getSide(): ?string
    {
        return $this->side;
    }

    public function setSide(?string $side): NextProductEventModel
    {
        $this->side = $side;
        return $this;
    }

    public function getPorscheOrderNumber(): ?string
    {
        return $this->porscheOrderNumber;
    }

    public function setPorscheOrderNumber(?string $porscheOrderNumber): NextProductEventModel
    {
        $this->porscheOrderNumber = $porscheOrderNumber;
        return $this;
    }

    public function getPorscheSequenceNumber(): ?string
    {
        return $this->porscheSequenceNumber;
    }

    public function setPorscheSequenceNumber(?string $porscheSequenceNumber): NextProductEventModel
    {
        $this->porscheSequenceNumber = $porscheSequenceNumber;
        return $this;
    }

    public function getArticleNumber(): ?string
    {
        return $this->articleNumber;
    }

    public function setArticleNumber(?string $articleNumber): NextProductEventModel
    {
        $this->articleNumber = $articleNumber;
        return $this;
    }
}
