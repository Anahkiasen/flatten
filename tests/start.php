<?php
// Start Flatten
Autoloader::namespaces(array(
  'Flatten' => Bundle::path('flatten') . 'libraries'
));

class FlattenTests extends PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass()
  {
    \Flatten\Config::replace(array('folder' => 'flattenTests'));
  }

  public static function tearDownAfterClass()
  {
    \File::rmdir(path('app').'cache'.DS.'flattenTests');
  }

  public function testSomething()
  {
    return $this->assertTrue(true);
  }
}