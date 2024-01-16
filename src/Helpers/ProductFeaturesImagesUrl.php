<?php

namespace AWF\Extension\Helpers;

class ProductFeaturesImagesUrl
{
    public static function getUrl(string $path): string
    {
        $url = '';
        $rootPath = ($_SERVER['HTTP_HOST'] ?? 'http://localhost') .'/storage/product/';

        if ($path !== null) {
            $url =  $rootPath . $path;
        }

        return $url;
    }
}
