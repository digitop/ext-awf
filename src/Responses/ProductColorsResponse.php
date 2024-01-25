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

        $this->workCenter->features()->where('WFSHNA', '=', 'OPSTATUS')->first()?->update([
            'WFVALU' => 'default',
        ]);
    }

    public function generate(): array
    {
        $data = [];
        $availableColors = [];

        foreach ($this->products as $product) {
            if (!in_array(
                $designation = $product?->features()->where('FESHNA', '=', 'TESZNE')->first()?->FEVALU,
                $availableColors,
                true
            )) {
                $data[] = [
                    'designation' => $designation,
                    'color' => $product?->features()->where('FESHNA', '=', 'SZASZ')->first()?->FEVALU,
                ];

                $availableColors[] = $designation;
            }
        }

        $data[] = [
            'status' => $this->workCenter?->features()->where('WFSHNA', '=', 'OPSTATUS')->first() ?? 'default',
        ];

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
