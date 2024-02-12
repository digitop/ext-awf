<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Interfaces\ResponseInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductColorsResponse implements ResponseInterface
{
    protected Collection $products;
    protected Model|null $workCenter = null;
    protected \stdClass|null $sequence = null;

    public function __construct(Collection|Model $products, Model|null $workCenter = null)
    {
        $this->products = $products;
        $this->workCenter = $workCenter;
    }

    public function generate(): array
    {
        $data = [];
        $availableColors = [];

        if (!empty($this->sequence)) {
            $sequenceProduct = DB::select(
                'select pgc.PGIDEN from PRPG pgc
                    where pgc.PRCODE = "' .
                $this->sequence->PRCODE . '"'
            );

            if (!empty($sequenceProduct[0])) {
                $sequenceProduct = $sequenceProduct[0];
            }
        }

        foreach ($this->products as $product) {
            if (!empty($sequenceProduct)) {
                $has = DB::select('
                select * from PRPG pgc
                 where pgc.PRCODE = "' . $product->PRCODE . '"' . ' and pgc.PGIDEN = ' . $sequenceProduct->PGIDEN
                );
            }

            if (
                (!empty($has) || !isset($has)) &&
                !in_array(
                    $designation = $product?->features()->where('FESHNA', '=', 'TESZNE')->first()?->FEVALU,
                    $availableColors,
                    true
                )
            ) {
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

    public function getSequence(): \stdClass|null
    {
        return $this->sequence;
    }

    public function setSequence(\stdClass|array|null $sequence): ProductColorsResponse
    {
        if (is_array($sequence) && array_key_exists(0, $sequence)) {
            $this->sequence = $sequence[0];
        }
        elseif (property_exists($sequence, 'ORCODE') || property_exists($sequence, 'PRCODE')) {
            $this->sequence = $sequence;
        }

        return $this;
    }
}
