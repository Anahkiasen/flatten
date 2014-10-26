<?php
namespace Flatten;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\FileLoader as ConfigLoader;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Philf\Setting\Setting;

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
		// Bind core classes
		$this->createStorageFolder();
		$this->bindCoreClasses();

		// Regisger package
		$this->app['config']->package('anahkiasen/flatten', __DIR__.'/../config');
		$this->bindFlattenClasses();

		if ($this->app->bound('artisan')) {
			$this->commands('flatten.commands.build');
		}
	}

	/**
	 * Boot Flatten
	 */
	public function boot()
	{
		// Register templating methods
		$this->app['flatten.templating']->registerTags();

		// Cancel if Flatten shouldn't run here
		if (!$this->app['flatten.context']->shouldRun()) {
			return false;
		}

		// Launch startup event
		$this->app['flatten']->start();

		// Bind closing event
		$app = $this->app;
		$this->app->finish(function ($request, $response) use ($app) {
			return $app['flatten']->end($response);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return string[]
	 */
	public function provides()
	{
		return array('flatten');
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// CLASS BINDINGS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Bind the core classes to the container
	 */
	protected function bindCoreClasses()
	{
		// Bind request
		$this->app->bindIf('request', function () {
			return Request::createFromGlobals();
		});

		// Bind config
		$this->app->bindIf('config', function ($app) {
			$fileloader = new ConfigLoader($app['files'], __DIR__.'/../');
			$config     = new Repository($fileloader, 'config');
			$config->set('cache.driver', 'file');
			$config->set('cache.path', __DIR__.'/../../cache');

			return $config;
		}, true);

		// Bind cache
		$this->app->bindIf('cache', function ($app) {
			return new CacheManager($app);
		});
	}

	/**
	 * Bind Flatten's classes to the container
	 */
	protected function bindFlattenClasses()
	{
		$this->app->bind('flatten', function ($app) {
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

		$this->app->singleton('flatten.cache', function ($app) {
			return new CacheHandler($app, $app['flatten']->computeHash());
		});

		$this->app->bind('flatten.storage', function ($app) {
			return new Setting($app['path.storage'].'/meta', 'flatten.json');
		});
	}

	/**
	 * Create the custom storage folder if it doesn't exist
	 */
	protected function createStorageFolder()
	{
		$this->app->bindIf('files', 'Illuminate\Filesystem\Filesystem');

		// Bind paths
		if (!$this->app->bound('path.storage')) {
			$storage                   = __DIR__.'/../../storage';
			$this->app['path.storage'] = $storage;
		}
	}
}
