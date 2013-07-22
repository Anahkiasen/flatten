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
    $this->app = new Container;

    $serviceProvider = new FlattenServiceProvider($this->app);
    $this->app = $serviceProvider->bindCoreClasses($this->app);
    $this->app = $serviceProvider->bindFlattenClasses($this->app);
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
}