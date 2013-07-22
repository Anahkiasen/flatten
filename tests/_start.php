<?php
use Flatten\Flatten;
use Illuminate\Container\Container;

abstract class FlattenTests extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->app = new Container;

    $serviceProvider = new FlattenServiceProvider($this->app);
    $this->app = $serviceProvider->bindCoreClasses($this->app);
    $this->app = $serviceProvider->bindFlattenClasses($this->app);
  }
}