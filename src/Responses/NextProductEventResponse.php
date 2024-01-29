<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Helpers\Models\NextProductEventModel;
use AWF\Extension\Interfaces\ResponseInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\PRODUCT;

class NextProductEventResponse  implements ResponseInterface
{
    protected Collection|Model $collection;
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
            $data[] = (new NextProductEventModel(
                PRODUCT::where()
            ))->get();
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
}
