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

    // Empty the cache
    $this->app['cache']->flush();
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
  	$instances = array(
			'cache'  => 'flatten.cache',
			'events' => 'flatten.events',
  	);

  	if (isset($instances[$key])) {
  		$key = $instances[$key];
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
   * Mock the Request component
   *
   * @param  string $url          Current URL
   *
   * @return Mockery
   */
  protected function mockRequest($url = null)
  {
    $request = Mockery::mock('Request');
    $request->shouldReceive('getMethod')->andReturn('GET');
    $request->shouldReceive('getPathInfo')->andReturn($url);

    return $request;
  }
}