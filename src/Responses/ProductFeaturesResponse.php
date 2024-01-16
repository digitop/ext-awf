<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Helpers\Models\ProductFeaturesResponseModel;
use AWF\Extension\Helpers\ProductFeaturesImagesUrl;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\PRODUCT;
use App\Models\WORKCENTER;

class ProductFeaturesResponse
{
    protected Collection $products;
    protected Model|null $workCenter = null;

    public function __construct(Collection $products, Model|null $workCenter = null)
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

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function setProducts(Collection $products): ProductFeaturesResponse
    {
        $this->products = $products;
        return $this;
    }

    public function getWorkCenter(): ?Model
    {
        return $this->workCenter;
    }

    public function setWorkCenter(?Model $workCenter): ProductFeaturesResponse
    {
        $this->workCenter = $workCenter;
        return $this;
    }
}
