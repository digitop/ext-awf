<?php

namespace AWF\Extension\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ResponseInterface
{
    public function __construct(Collection|Model $collection, Model|null $model);
    public function generate(): array;
    public function getCollection(): Collection|Model;
    public function setCollection(Collection|Model $collection): ResponseInterface;
    public function getModel(): Model|null;
    public function setModel(Model|null $model): ResponseInterface;
}
