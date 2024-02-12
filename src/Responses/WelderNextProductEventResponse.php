<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Helpers\Models\NextProductEventModel;
use AWF\Extension\Interfaces\ResponseInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\PRODUCT;
use Illuminate\Support\Facades\Storage;

class WelderNextProductEventResponse  implements ResponseInterface
{
    protected Collection|Model $collection;
    protected Collection|Model|null $next;
    protected Model|null $model;

    public function __construct(Model|Collection $collection, Model|null $model)
    {
        $this->collection = $collection;
        $this->model = $model;
    }

    public function generate(): array
    {
        $data = [];

        foreach ($this->collection as $item) {
            $product = PRODUCT::where('PRCODE', '=', $item->PRCODE)->first();

            $data['current'] = (new NextProductEventModel(
                $product->PRNAME,
                $product->features()->where('FESHNA', '=', 'SZASZ')->first()?->FEVALU,
                $product->features()->where('FESHNA', '=', 'TESZNE')->first()?->FEVALU,
            ))
                ->get();

            $data['sequenceId'] = $item->SEQUID;
        }

        foreach ($this->next as $item) {
            $product = PRODUCT::where('PRCODE', '=', $item->PRCODE)->first();

            $imagePath = $product->features()->where('FESHNA', '=', 'TEKEHE')->first()?->FEVALU;

            if (!empty($imagePath)) {
                $imagePath = Storage::disk('products')->url($imagePath);
            }

            $data['next'] = (new NextProductEventModel(
                $product->PRNAME,
                $product->features()->where('FESHNA', '=', 'SZASZ')->first()?->FEVALU,
                $product->features()->where('FESHNA', '=', 'TESZNE')->first()?->FEVALU,
            ))
                ->setImage(
                    $imagePath,
                )
                ->get();
        }

        return $data;
    }

    public function getCollection(): Collection|Model
    {
        return $this->collection;
    }

    public function setCollection(Model|Collection $collection): ResponseInterface
    {
        $this->collection = $collection;
        return $this;
    }

    public function getModel(): Model|null
    {
        return $this->model;
    }

    public function setModel(Model|null $model): ResponseInterface
    {
        $this->model = $model;
        return $this;
    }

    public function getNext(): Model|Collection|null
    {
        return $this->next;
    }

    public function setNext(Model|Collection|null $next): WelderNextProductEventResponse
    {
        $this->next = $next;
        return $this;
    }
}
