<?php
namespace Paulodiff\RainbowTableIndex;

use Illuminate\Support\ServiceProvider;
use Paulodiff\RainbowTableIndex\Console\RainbowTableIndexKeyGeneratorCommand;
use Paulodiff\RainbowTableIndex\Console\RainbowTableIndexCheckConfigCommand;
use Paulodiff\RainbowTableIndex\Console\RainbowTableIndexDbSeedCommand;
use Paulodiff\RainbowTableIndex\Console\RainbowTableIndexDbCrudCommand;
use Paulodiff\RainbowTableIndex\Console\RainbowTableIndexDbMaintenanceCommand;

class RainbowTableIndexServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-package-demo');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-package-demo');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/rainbowtableindex.php' => config_path('rainbowtableindex.php'),
            ], 'config');


            if ($this->app->runningInConsole()) {
                $this->commands([
                    RainbowTableIndexKeyGeneratorCommand::class,
                    RainbowTableIndexCheckConfigCommand::class,
                    RainbowTableIndexDbSeedCommand::class,
                    RainbowTableIndexDbCrudCommand::class,
                    RainbowTableIndexDbMaintenanceCommand::class,
                ]);
            }

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-package-demo'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-package-demo'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-package-demo'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/rainbowtableindex.php', 'rainbox-table-index');

        // Register the main class to use with the facade
        $this->app->singleton('rainbox-table-index', function () {
            return new RainbowTableIndex;
        });
    }
}
