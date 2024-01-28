<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Helpers\Models\ProductFeaturesResponseModel;
use AWF\Extension\Helpers\ProductFeaturesImagesUrl;
use AWF\Extension\Interfaces\ResponseInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\PRODUCT;
use App\Models\WORKCENTER;

class ProductFeaturesResponse implements ResponseInterface
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

        foreach ($this->products as $product) {
            $data[$product->PRCODE][] = (new ProductFeaturesResponseModel(
                $product?->features()->where('FESHNA', '=', 'SZASZ')->first()?->FEVALU,
                $product?->features()->where('FESHNA', '=', 'SZAA')->first()?->FEVALU
            ))
                ->setColorDesignation($product?->features()->where('FESHNA', '=', 'TESZNE')->first()?->FEVALU)
                ->setPreparatoryImageUrl(ProductFeaturesImagesUrl::getUrl(
                    $product?->features()->where('FESHNA', '=', 'TEKEEL')->first()?->FEVALU
                ))
                ->setWelderImageUrl(ProductFeaturesImagesUrl::getUrl(
                    $product?->features()->where('FESHNA', '=', 'TEKEHE')->first()?->FEVALU
                ))
                ->get();
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
