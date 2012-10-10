<?php
/**
 * Config
 *
 * Options for Flatten
 */
namespace Flatten;

use \Config as Conf;

class Config
{
  /**
   * The current options of Flatten
   * @var array
   */
  private static $options = array();

  /**
   * Fetch options from both Flatten and the user
   */
  public function __construct()
  {
    $defaultOptions = Conf::get('flatten::flatten');
    $userOptions = (array) Conf::get('flatten');

    static::$options = array_merge($defaultOptions, $userOptions);
  }

  /**
   * Get the value of an option
   *
   * @param  string $key      The option to get
   * @param  string $fallback A fallback if undefined
   * @return string           The option value
   */
  public static function get($key = null, $fallback = null)
  {
    if(!$key) return static::$options;

    return array_get(static::$options, $key, $fallback);
  }

  /**
   * Set an option
   *
   * @param string $key   The option to set
   * @param string $value Its new value
   */
  public static function set($key, $value)
  {
    static::$options[$key] = $value;
  }

  /**
   * Replace the current array of options with another
   *
   * @param  array $options The new options
   */
  public static function replace($options)
  {
    static::$options = $options;
  }
}
