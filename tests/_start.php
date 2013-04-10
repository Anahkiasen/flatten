<?php
ini_set('memory_limit', '120M');

use Flatten\Flatten;
use Illuminate\Container\Container;

abstract class FlattenTests extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->app = new Container;
    $this->app = Flatten::bind($this->app);
  }
}