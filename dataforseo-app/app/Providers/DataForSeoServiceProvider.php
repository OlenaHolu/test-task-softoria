<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DataForSeoService;
use App\Services\DataForSeo\DataForSeoClient;
use App\Services\DataForSeo\MetadataService;
use App\Services\DataForSeo\SerpService;
use App\Services\DataForSeo\SerpAnalyzer;

class DataForSeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DataForSeoClient::class);
        $this->app->singleton(SerpAnalyzer::class);
        
        $this->app->singleton(MetadataService::class, function ($app) {
            return new MetadataService($app->make(DataForSeoClient::class));
        });
        
        $this->app->singleton(SerpService::class, function ($app) {
            return new SerpService(
                $app->make(DataForSeoClient::class),
                $app->make(SerpAnalyzer::class)
            );
        });
        
        $this->app->singleton(DataForSeoService::class, function ($app) {
            return new DataForSeoService(
                $app->make(SerpService::class),
                $app->make(MetadataService::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}