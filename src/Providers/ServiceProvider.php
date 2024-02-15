<?php

namespace AWF\Extension\Providers;

use AWF\Extension\Commands\GenerateDataCommand;
use AWF\Extension\Helpers\Facades\Controllers\Api\ScrapFacade;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Illuminate\Support\Facades\Event;

class ServiceProvider extends IlluminateServiceProvider
{
    protected string $tagName = 'awf-extension';

    public function boot()
    {
        include __DIR__ . '/../routes/api.php';
        include __DIR__ . '/../routes/web.php';

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../views', $this->tagName);

        $this->publishes(
            [__DIR__ . '/../database/migrations' => database_path('migrations/extensions/' . $this->tagName)],
            $this->tagName
        );
        $this->publishes(
            [__DIR__ . '/../js' => public_path('vendor/oeem-extensions/awf/extension/js')],
            $this->tagName
        );
        $this->publishes(
            [__DIR__ . '/../css' => public_path('vendor/oeem-extensions/awf/extension/css')],
            $this->tagName
        );
        $this->publishes(
            [__DIR__ . '/../images' => public_path('vendor/oeem-extensions/awf/extension/images')],
            $this->tagName
        );
        $this->publishes(
            [
                __DIR__ . '/../storage/sequence-data' =>
                    storage_path('app/public/' . config('storage.file.path.sequense-data.save'))
            ],
            $this->tagName
        );
        $this->publishes([__DIR__ . '/../lang' => resource_path('lang/')], $this->tagName);

        Event::listen('*', function ($event, $data) {
            switch ($event) {
                case 'App\\Events\\Dashboard\\ProductQualified':
                    if (array_key_exists(0, $data) && !empty($data[0])) {
                        (new ScrapFacade())->index($data[0]);
                    }
                    break;
                default:
                    break;
            }
        });

        $this->app->config["filesystems.disks.awfSequenceFtp"] = [
            'driver' => 'ftp',
            'host' => '',
            'username' => '',
            'password' => '',
            'port' => '21', //
            'root' => '/',
            'ssl' => false,
            'timeout' => 30
        ];

        $this->app->config["filesystems.disks.products"] = [
            'driver' => 'local',
            'root' => storage_path('app/public/product'),
            'url' => env('APP_URL') . '/storage/product',
            'visibility' => 'public',
            'throw' => true,
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->commands([GenerateDataCommand::class]);
    }
}
