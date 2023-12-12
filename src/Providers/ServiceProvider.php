<?php

namespace AWF\Extension\Providers;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    protected $tagname = 'awf-extension';

    public function boot()
    {
        include __DIR__ . '/../routes/api.php';

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../views', $this->tagname);

        $this->publishes(
            [__DIR__ . '/../js' => public_path('vendor/oeem-extensions/awf/extension/js')],
            $this->tagname
        );
        $this->publishes(
            [__DIR__ . '/../css' => public_path('vendor/oeem-extensions/awf/extension/css')],
            $this->tagname
        );
        $this->publishes(
            [
                __DIR__ . '/../storage/sequence-data' =>
                    storage_path('app/public/' . config('storage.file.path.sequense-data.save'))
            ],
            $this->tagname
        );
        $this->publishes([__DIR__ . '/../lang' => resource_path('lang/')], $this->tagname);

        $this->app->config["filesystems.disks.awfSequenceFtp"] = [
            'driver' => 'ftp',
            'host' => '',
            'username' => '',
            'password' => '',
            'port' => 21,
            'root' => '/as400/',
            'ssl' => false,
            'timeout' => 30
        ];
    }
}
