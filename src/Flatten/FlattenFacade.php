<?php
/**
 * Flatten
 *
 * Flatten facade for the Laravel framework
 */
namespace Flatten;

use Illuminate\Support\Facades\Facade;

class Flatten extends Facade
{
  /**
   * Get the registered component.
   *
   * @return object
   */
  protected static function getFacadeAccessor()
  {
    return 'flatten';
  }
}
