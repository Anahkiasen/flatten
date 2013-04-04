<?php
namespace Flatten;

use Illuminate\Support\ServiceProvider;

/**
 * Register the Flatten package with the Laravel framework
 */
class FlattenServiceProvider extends ServiceProvider
{

  /**
   * Register Flatten's classes with Laravel
   */
  public function register()
  {
    $this->app['config']->package('anahkiasen/flatten', __DIR__.'/../../config');

    $this->app->bind('flatten', function($app) {
       return new Flatten($app);
    });

    $this->app->bind('flatten.events', function($app) {
      return new EventHandler($app);
    });
  }

  /**
   * Boot Flatten
   */
  public function boot()
  {
    // Cancel if Flatten shouldn't run here
    if (!$this->app['flatten']->shouldRun()) {
      return false;
    }

    $this->app['flatten.events']->onApplicationBoot();

    $app = $this->app;
    $this->app->finish(function() use ($app) {
      return $app['flatten.events']->onApplicationDone();
    });
  }

  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides()
  {
    return array('flatten');
  }

}
