<?php
require __DIR__.'/../vendor/autoload.php';

use Flatten\FlattenServiceProvider;
use Illuminate\Container\Container;

abstract class FlattenTests extends PHPUnit_Framework_TestCase
{
	/**
	 * The Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Set up the tests
	 */
	public function setUp()
	{
		// Create Container
		$this->app = FlattenServiceProvider::make();
		$this->app['url'] = $this->mockUrl();

		// Empty the cache
		$this->app['cache']->flush();
		$this->storage->clear();
		$this->context->inConsole(false);
	}

	/**
	 * Get an instance from the Container
	 *
	 * @param  string $key
	 *
	 * @return object
	 */
	public function __get($key)
	{
		$aliases = array('cache', 'context', 'events', 'storage');
		if (in_array($key, $aliases)) {
			$key = 'flatten.'.$key;
		}

		return $this->app[$key];
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// MOCKED INSTANCES ///////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Mock the Config repository
	 *
	 * @param array $options
	 *
	 * @return Mockery
	 */
	protected function mockConfig($options = array())
	{
		$config = Mockery::mock('Config');
		foreach ($options as $option => $value) {
			$config->shouldReceive('get')->with($option)->andReturn($value);
		}

		return $config;
	}

	/**
	 * Mock the UrlGenerator component
	 *
	 * @return Mockery
	 */
	protected function mockUrl()
	{
		$url = Mockery::mock('UrlGenerator');

		return $url;
	}

	/**
	 * Mock the Request component
	 *
	 * @param  string $url          Current URL
	 *
	 * @return Mockery
	 */
	protected function mockRequest($url = null)
	{
		$request = Mockery::mock('Request');
		$request->shouldReceive('root')->andReturn('http://localhost');
		$request->shouldReceive('getMethod')->andReturn('GET');
		$request->shouldReceive('getPathInfo')->andReturn('http://localhost'.$url);
		$request->shouldReceive('path')->andReturn(ltrim($url, '/'));

		return $request;
	}
}
