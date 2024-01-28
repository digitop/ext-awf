<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Interfaces\ResponseInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProductColorsResponse implements ResponseInterface
{
    protected Collection $products;
    protected Model|null $workCenter = null;

    public function __construct(Collection|Model $products, Model|null $workCenter = null)
    {
        $this->products = $products;
        $this->workCenter = $workCenter;
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

        return $data;
    }

    public function getCollection(): Collection|Model
    {
        return $this->products;
    }

    public function setCollection(Collection|Model $products): ResponseInterface
    {
        $this->products = $products;
        return $this;
    }

    public function getModel(): Model|null
    {
        return $this->workCenter;
    }

    public function setModel(Model|null $workCenter): ResponseInterface
    {
        $this->workCenter = $workCenter;
        return $this;
    }
}
