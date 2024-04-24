<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Helpers\Models\ShiftManagementResettingEventModel;
use AWF\Extension\Interfaces\NullableResponseInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ShiftManagementResettingEventResponse implements NullableResponseInterface
{
    protected Collection|Model|null $collection = null;
    protected Model|null $model = null;

    public function __construct(Collection|Model|null $collection = null, Model|null $model = null)
    {
        $this->collection = $collection;
        $this->model = $model;
    }

    public function generate(): array
    {
        return (new ShiftManagementResettingEventModel(
            true,
            ShiftManagementResettingEventModel::DEFAULT
        ))
            ->get();
    }

    public function getCollection(): Model|Collection|null
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
}
