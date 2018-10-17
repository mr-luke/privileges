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
        $this->publishes([__DIR__ .'/../config/privileges.php' => config_path('privileges.php')]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ .'/../config/privileges.php', 'privileges');

        $this->app->singleton('mrluke-privileges', function ($app) {
            return new \Mrluke\Privileges\Detector($app['config']->get('privileges'));
        });
    }
}
