<?php

namespace Flatten;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

/**
 * Register the Flatten package with the Laravel framework.
 */
class FlattenServiceProvider extends ServiceProvider
{
    /**
     * Register Flatten's classes with Laravel.
     */
    public function register()
    {
        // Bind core classes
        $this->createStorageFolder();
        $this->bindCoreClasses();

        $this->mergeConfigFrom($this->getConfigurationPath(), 'flatten');

        // Regisger package
        $this->bindFlattenClasses();

        if ($this->app->bound('events')) {
            $this->commands('flatten.commands.build');
        }
    }

    /**
     * Boot Flatten.
     */
    public function boot()
    {
        $this->publishes([
           $this->getConfigurationPath() => config_path('flatten.php')
        ]);

        $this->app['flatten.templating']->registerTags();
    }

    /**
     * @return string
     */
    protected function getConfigurationPath()
    {
        return  __DIR__.'/../config/flatten.php';
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['flatten'];
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////// CLASS BINDINGS /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Bind the core classes to the container.
     */
    protected function bindCoreClasses()
    {
        // Bind request
        $this->app->bindIf('request', function () {
            return Request::createFromGlobals();
        });

        // Bind config
        $this->app->bindIf('config', function ($app) {
            return new Repository([
                'cache' => [
                    'default' => 'file',
                    'stores'  => [
                        'file' => [
                            'driver' => 'file',
                            'path'   => $app['path.storage'],
                        ],
                    ],
                ],
            ]);
        }, true);

        // Bind cache
        $this->app->bindIf('cache', function ($app) {
            return new CacheManager($app);
        });
    }

    /**
     * Bind Flatten's classes to the container.
     */
    protected function bindFlattenClasses()
    {
        $this->app->alias('Flatten\Flatten', 'flatten');
        $this->app->bind('Flatten\Flatten', function ($app) {
            return new Flatten($app);
        });

        $this->app->bind('flatten.commands.build', function () {
            return new Crawler\BuildCommand();
        });

        $this->app->singleton('flatten.context', function ($app) {
            return new Context($app);
        });

        $this->app->bind('flatten.events', function ($app) {
            return new EventHandler($app);
        });

        $this->app->bind('flatten.templating', function ($app) {
            return new Templating($app);
        });

        $this->app->alias('Flatten\CacheHandler', 'flatten.cache');
        $this->app->singleton('Flatten\CacheHandler', function ($app) {
            return new CacheHandler($app, $app['flatten']->computeHash());
        });

        $this->app->bind('flatten.storage', function ($app) {
            $storage = $app['path.storage'];
            $frameworkPath = $storage.DIRECTORY_SEPARATOR.'app';
            $path = is_dir($frameworkPath) ? $frameworkPath : $storage;

            return new Metadata($path);
        });
    }

    /**
     * Create the custom storage folder if it doesn't exist.
     */
    protected function createStorageFolder()
    {
        $this->app->bindIf('files', 'Illuminate\Filesystem\Filesystem');

        // Bind paths
        if (!$this->app->bound('path.storage')) {
            $storage                   = __DIR__.'/../cache';
            $this->app['path.storage'] = $storage;
        }
    }
}
