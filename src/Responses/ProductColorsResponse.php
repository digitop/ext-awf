<?php

namespace AWF\Extension\Responses;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
                $data['color'][] = $color;
            }

            if (!in_array(
                $designation = $product?->features()->where('FESHNA', '=', 'TESZNE')->first()?->FEVALU,
                $data,
                true
            )) {
                $data['designation'][] = $color;
            }
        }

        return $data;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function setProducts(Collection $products): ProductColorsResponse
    {
        $this->products = $products;
        return $this;
    }

    public function getWorkCenter(): Model|null
    {
        return $this->workCenter;
    }

    public function setWorkCenter(Model|null $workCenter): ProductColorsResponse
    {
        $this->workCenter = $workCenter;
        return $this;
    }
}
