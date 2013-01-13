<?php
/**
 * FlattenServiceProvider
 *
 * Register the Flatten package with the Laravel framework
 */
namespace Flatten;

use Illuminate\Support\ServiceProvider;

class FlattenServiceProvider extends ServiceProvider
{
  public function register()
  {
    // Register config file
    $this->app['config']->package('anahkiasen/flatten', __DIR__.'/../config');

    $this->app['flatten'] = new Flatten($this->app);

    // Bind Flatten events to Illuminate
    $this->app['events']->subscribe(new EventHandler($this->app));
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
