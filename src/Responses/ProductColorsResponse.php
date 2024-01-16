<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Helpers\Models\ProductFeaturesResponseModel;
use AWF\Extension\Helpers\ProductFeaturesImagesUrl;

class ProductColorsResponse
{
    protected Collection $products;
    protected Model|null $workCenter = null;

    public function __construct(Collection $products, Model|null $workCenter = null)
    {
        $this->products = $products;
        $this->workCenter = $workCenter;
    }

    public function generate(): array
    {
        $data = [];

        foreach ($this->products as $product) {
            if (
                !in_array($color = $product?->features()->where('FESHNA', '=', 'SZASZ')->first()?->FEVALU, $data, true)
            ) {
                $data[] = $color;
            }
        }

        return $data;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function setProducts(Collection $products): ProductFeaturesResponse
    {
        $this->products = $products;
        return $this;
    }

    public function getWorkCenter(): ?Model
    {
        return $this->workCenter;
    }

    public function setWorkCenter(?Model $workCenter): ProductFeaturesResponse
    {
        $this->workCenter = $workCenter;
        return $this;
    }
}
