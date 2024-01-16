<?php

namespace AWF\Extension\Helpers\Models;

class ProductFeaturesResponseModel extends ObjectToArray
{
    protected string|null $color = null;
    protected string|null $colorDesignation = null;
    protected string|null $material = null;
    protected string|null $preparatoryImageUrl = null;
    protected string|null $welderImageUrl = null;

    public function __construct(string|null $color, string|null $material)
    {
        $this->color = $color;
        $this->material = $material;
    }

    public function getColor(): string|null
    {
        return $this->color;
    }

    public function setColor(string|null $color): ProductFeaturesResponseModel
    {
        $this->color = $color;
        return $this;
    }

    public function getColorDesignation(): ?string
    {
        return $this->colorDesignation;
    }

    public function setColorDesignation(?string $colorDesignation): ProductFeaturesResponseModel
    {
        $this->colorDesignation = $colorDesignation;
        return $this;
    }

    public function getMaterial(): string|null
    {
        return $this->material;
    }

    public function setMaterial(string|null $material): ProductFeaturesResponseModel
    {
        $this->material = $material;
        return $this;
    }

    public function getPreparatoryImageUrl(): string|null
    {
        return $this->preparatoryImageUrl;
    }

    public function setPreparatoryImageUrl(string|null $preparatoryImageUrl): ProductFeaturesResponseModel
    {
        $this->preparatoryImageUrl = $preparatoryImageUrl;
        return $this;
    }

    public function getWelderImageUrl(): string|null
    {
        return $this->welderImageUrl;
    }

    public function setWelderImageUrl(string|null $welderImageUrl): ProductFeaturesResponseModel
    {
        $this->welderImageUrl = $welderImageUrl;
        return $this;
    }
}
