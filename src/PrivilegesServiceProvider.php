<?php

namespace Mrluke\Privileges;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

/**
 * ServiceProvider for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/searcher
 *
 * @category  Laravel
 * @package   mr-luke/privileges
 * @license   MIT
 */
class PrivilegesServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([__DIR__ .'/../config/privileges.php' => config_path('privileges.php')], 'config');

        $this->publishes([__DIR__.'/../database/migrations/' => database_path('migrations')], 'migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ .'/../config/privileges.php', 'privileges');

        $this->app->singleton('mrluke-privileges-detector', function ($app) {

            return new \Mrluke\Privileges\Detector;
        });

        $this->app->singleton('mrluke-privileges-manager', function ($app) {

            $schema = \Mrluke\Configuration\Schema::createFromFile(
                __DIR__.'/../configuration/schema.json',
                true
            );

            $config = new \Mrluke\Configuration\Host(
                $app['config']->get('privileges'),
                $schema
            );

            return new \Mrluke\Privileges\Manager($config);
        });
    }
}
