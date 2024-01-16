<?php

namespace AWF\Extension\Responses;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProductMaterialsResponse
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
            if (!in_array(
                $material = $product?->features()->where('FESHNA', '=', 'SZAA')->first()?->FEVALU,
                $data,
                true
            )) {
                $data[] = ucfirst($material);
            }
        }

        return $data;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function setProducts(Collection $products): ProductMaterialsResponse
    {
        $this->products = $products;
        return $this;
    }

    public function getWorkCenter(): ?Model
    {
        return $this->workCenter;
    }

    public function setWorkCenter(?Model $workCenter): ProductMaterialsResponse
    {
        $this->workCenter = $workCenter;
        return $this;
    }
}
