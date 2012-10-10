<?php
use \Flatten\Flatten;
use \Flatten\Config;

include 'start.php';

class FlattenTest extends FlattenTests
{
  public function testIgnore()
  {
    Config::set('ignore', array('foo'));
    Flatten::setHash('foo');
    $hook = Flatten::hook();

    $this->assertFalse($hook);
  }

  public function testIgnoreFail()
  {
    Config::set('ignore', array('bar'));
    Flatten::setHash('foo');
    $hook = Flatten::hook();

    $this->assertTrue($hook);
  }

  public function testOnly()
  {
    Config::set('only', array('foo'));
    Flatten::setHash('foo');
    $hook = Flatten::hook();

    $this->assertTrue($hook);
  }

  public function testOnlyFail()
  {
    Config::set('only', array('foo'));
    Flatten::setHash('bar');
    $hook = Flatten::hook();

    $this->assertFalse($hook);
  }
}