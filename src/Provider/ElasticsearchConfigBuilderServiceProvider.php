<?php
namespace Triadev\EsConfigBuilder\Provider;

use Illuminate\Support\ServiceProvider;
use Triadev\EsConfigBuilder\Contract\ElasticsearchConfigBuilderContract;
use Triadev\EsConfigBuilder\ElasticsearchConfigBuilder;

class ElasticsearchConfigBuilderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath(__DIR__ . '/../Config/config.php');
        
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('triadev-elasticsearch-config-builder.php'),
        ], 'config');
        
        $this->mergeConfigFrom($source, 'triadev-elasticsearch-config-builder');
    }
    
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ElasticsearchConfigBuilderContract::class, function () {
            return app()->make(ElasticsearchConfigBuilder::class);
        });
    }
}
