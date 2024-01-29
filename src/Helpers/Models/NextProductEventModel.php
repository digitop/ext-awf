<?php

namespace AWF\Extension\Helpers\Models;

class NextProductEventModel extends ObjectToArray
{
    protected string|null $designation = null;
    protected string|null $color = null;
    protected string|null $materialAndColor = null;

    public function __construct(string|null $designation = null,
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
}
