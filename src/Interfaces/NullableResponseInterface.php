<?php

namespace AWF\Extension\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface NullableResponseInterface
{
    public function __construct(Collection|Model|null $collection = null, Model|null $model = null);
    public function generate(): array;
    public function getCollection(): Collection|Model|null;
    public function setCollection(Collection|Model|null $collection = null): NullableResponseInterface;
    public function getModel(): Model|null;
    public function setModel(Model|null $model = null): NullableResponseInterface;
}
