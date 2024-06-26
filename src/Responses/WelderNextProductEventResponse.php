<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Helpers\Models\NextProductEventModel;
use AWF\Extension\Interfaces\NullableResponseInterface;
use AWF\Extension\Interfaces\ResponseInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\PRODUCT;
use Illuminate\Support\Facades\Storage;

class WelderNextProductEventResponse  implements NullableResponseInterface
{
    protected Collection|Model|null $collection;
    protected Collection|Model|null $next;
    protected Model|null $model;
    protected bool $startShift = false;

    public function __construct(Model|Collection|null $collection = null, Model|null $model = null)
    {
        $this->collection = $collection;
        $this->model = $model;
    }

    public function generate(): array
    {
        $data = [];

        if (empty($this->collection)) {
            return $data;
        }

        foreach ($this->collection as $item) {
            $product = PRODUCT::where('PRCODE', '=', $item->PRCODE)->first();

            $data['current'] = (new NextProductEventModel(
                $product->PRNAME,
                $product->features()->where('FESHNA', '=', 'SZASZ')->first()?->FEVALU,
                $product->features()->where('FESHNA', '=', 'TESZNE')->first()?->FEVALU,
            ))
                ->setArticleNumber($item->ORCODE)
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
                ->setArticleNumber($item->ORCODE)
                ->setImage(
                    $imagePath,
                )
                ->get();
        }

        $workCenter = null;

        if ($this->collection[0]->SEPILL === 'A') {
            $workCenter = 'HAB01';
        }

        if ($this->collection[0]->SEPILL === 'B') {
            $workCenter = 'HBB01';
        }

        if ($this->collection[0]->SEPILL === 'C') {
            $workCenter = 'HCB01';
        }

        $data['workCenter'] = $workCenter;
        $data['startShift'] = $this->startShift;

        return $data;
    }

    public function getCollection(): Collection|Model
    {
        return $this->collection;
    }

    public function setCollection(Model|Collection|null $collection = null): NullableResponseInterface
    {
        $this->collection = $collection;
        return $this;
    }

    public function getModel(): Model|null
    {
        return $this->model;
    }

    public function setModel(Model|null $model = null): NullableResponseInterface
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

    public function isStartShift(): bool
    {
        return $this->startShift;
    }

    public function setStartShift(bool $startShift): WelderNextProductEventResponse
    {
        $this->startShift = $startShift;
        return $this;
    }
}
