<?php

namespace Eddie\TencentIm\Provider;

use Eddie\TencentIm\Im;
use Illuminate\Support\ServiceProvider;

class ImServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->handleConfigs();

        //$this->handleMigrations();

        //$this->handleViews();

        //$this->handleTranslations();

        //$this->handleRoutes();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('tencent.im', function ($app) {
            return new Im($app->config);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['tencent.im'];
    }


    /**
     * @author Eddie
     */
    private function handleConfigs()
    {
        /*
         * Config path.
         */
        $configPath = realpath(__DIR__ . '/../../config/im.php');

        /*
         * Publish config file.
         */
        $this->publishes([$configPath => config_path('im.php')], 'config');

        /*
         * Merge config file.
         */
        $this->mergeConfigFrom($configPath, 'im');
    }

    /**
     * @author Eddie
     */
    private function handleMigrations()
    {
        // TODO
    }

    /**
     * @author Eddie
     */
    private function handleViews()
    {
        // TODO
    }

    /**
     * @author Eddie
     */
    private function handleTranslations()
    {
        // TODO
    }

    /**
     * @author Eddie
     */
    private function handleRoutes()
    {
        // TODO
    }

}
