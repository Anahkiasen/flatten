<?php
namespace Flatten;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\FileLoader as ConfigLoader;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

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
    $this->app['config']->package('anahkiasen/flatten', __DIR__.'/../config');

    // Bind the classes
    $this->app = static::make($this->app);

    $this->commands('flatten.commands.build');
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

    // Launch startup event
    $this->app['flatten.events']->onApplicationBoot();

    // Bind closing event
    $app = $this->app;
    $this->app->finish(function($request, $response) use ($app) {
      return $app['flatten.events']->onApplicationDone($response);
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

  ////////////////////////////////////////////////////////////////////
  /////////////////////////// CLASS BINDINGS /////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Create the Flatten Container
   *
   * @param  Container $app
   *
   * @return Container
   */
  public static function make(Container $app = null)
  {
    if (!$app) {
      $app = new Container;
    }

    $provider = new static($app);
    $app = $provider->bindCoreClasses($app);
    $app = $provider->bindFlattenClasses($app);

    return $app;
  }

  /**
   * Bind the core classes to the container
   *
   * @param  Container $app
   *
   * @return Container
   */
  public function bindCoreClasses(Container $app)
  {
    $app->bindIf('request', function() {
      return Request::createFromGlobals();
    });

    $app->bindIf('files', 'Illuminate\Filesystem\Filesystem');

    $app->bindIf('config', function($app) {
      $fileloader = new ConfigLoader($app['files'], __DIR__.'/../');
      $config = new Repository($fileloader, 'config');
      $config->set('cache.driver', 'file');
      $config->set('cache.path', __DIR__.'/../../cache');

      return $config;
    }, true);

    $app->bindIf('cache', function($app) {
      return new CacheManager($app);
    });

    return $app;
  }

  /**
   * Bind Flatten's classes to the container
   *
   * @param  Container $app
   *
   * @return Container
   */
  public function bindFlattenClasses(Container $app)
  {
    $app->bind('flatten', function($app) {
       return new Flatten($app);
    });

    $app->bind('flatten.commands.build', function($app) {
      return new Crawler\BuildCommand;
    });

    $app->bind('flatten.events', function($app) {
      return new EventHandler($app);
    });

    $app->bind('flatten.cache', function($app) {
      return new CacheHandler($app, $app['flatten']->computeHash());
    });

    return $app;
  }
}
