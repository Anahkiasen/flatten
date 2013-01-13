<?php
namespace Flatten;

use \Cache;
use \Event;

class Flatten
{
  /**
   * The current URL
   * @var string
   */
  private $hash = null;

  /**
   * The current language
   * @var string
   */
  private $lang = null;

  /**
   * Setup Flatten and hook it to the application
   *
   * @param Illuminate\Container\Container $app
   */
  public function __construct($app)
  {
    $this->app = $app;

    $this->hook();
  }

  /**
   * Hook Flatten to Laravel's events
   *
   * @return boolean Whether Flatten started caching or not
   */
  public function hook()
  {
    // If we're in the console, cancel
    if($this->app->runningInConsole()) return false;

    // Check if we're in an allowed environment
    if (!$this->isInAllowedEnvironment()) return false;

    // Set cache language
    //preg_match_all("#^([a-z]{2})/.+#i", $this->app['uri']->current(), $language);
    //$this->lang = array_get($language, '1.0', $this->app['config']->get('flatten::app.language'));

    // Get pages to cache
    $only    = $this->app['config']->get('flatten::only');
    $ignored = $this->app['config']->get('flatten::ignore');
    $cache   = false;

    // Ignore and only
    if (!$ignored and !$only) $cache = true;
    else {
      if ($only    and  $this->matches($only))    $cache = true;
      if ($ignored and !$this->matches($ignored)) $cache = true;
    }

    // Launch cache process
    if ($cache) {
      //$this->load();
      //$this->save();

      return true;
    }
  }

  // Caching functions --------------------------------------------- /

  /*
  public function cache_route($route, $parameters = array())
  {
    // Get the controller's render
    $render = \Route::forward('GET', $route);

    // Create file hash from action
    $url = $route;
    if($parameters) $url .= '/'.implode('/', $parameters);

    // Cache render
    $this->app['cache']->forever($this->hash($url), $render->call());
  }

  public function cache_action($action, $parameters = array())
  {
    // Create file hash from action
    $url = str_replace('@', '/', $action);
    if($parameters) $url .= '/'.implode('/', $parameters);

    // Queue the content to cache
    $this->queue['controller'][$url] = array($action, $parameters);
  }
  */

  // Flushing functions -------------------------------------------- /

  /**
   * Empties the pages in cache
   *
   * @return boolean Whether the operation succeeded or not
   */
  public function flush($pattern = null)
  {
    $folder = path('storage').'cache'.DS.$this->app['config']->get('flatten::folder');

    // Delete only certain files
    if ($pattern) {
      $pattern = str_replace('/', '_', $pattern);
      $files = glob($folder.DS.'*'.$pattern.'*');
      foreach($files as $file) $this->app['file']->delete($file);

      return sizeof($files);
    }

    return $this->app['file']->cleandir($folder);
  }

  /**
   * Flush a specific action
   *
   * @param  string  $action     An action
   * @param  array   $parameters Parameters to pass it
   * @return boolean             Success or not
   */
  public function flush_action($action, $parameters = array())
  {
    // Get matching URL
    $url = action($action, $parameters);
    $url = $this->urlToPattern($url);

    // Flush any cache found with that pattern
    return $this->flush($url);
  }

  /**
   * Flush a specific route
   *
   * @param  string  $route      A route
   * @param  array   $parameters Parameters to pass it
   * @return boolean             Success or not
   */
  public function flush_route($route, $parameters = array())
  {
    // Get matching URL
    $url = route($route, $parameters);
    $url = $this->urlToPattern($url);

    // Flush any cache found with that pattern
    return $this->flush($url);
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////////// HELPERS ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Check if the current environment is allowed to be cached
   *
   * @return boolean
   */
  private function isInAllowedEnvironment()
  {
    // Get allowed environments
    $environment = $this->app['env'];
    $allowed = (array) $this->app['config']->get('flatten::environments');

    // Check if the current one is allowed
    if(in_array($environment, $allowed)) return false;

    return true;
  }

  /**
   * Transforms an URL into a Regex pattern
   *
   * @param  string $url An url to transform
   * @return string      A transformed URL
   */
  private function urlToPattern($url)
  {
    // Remove the base from the URL
    $url = str_replace($this->app['url']->base().'/', null, $url);

    // Remove language-specific pattern if any
    $url = preg_replace('#[a-z]{2}/(.+)#', '$1', $url);

    return $url;
  }

  /**
   * Get the current page's hash
   *
   * @return string A page hash
   */
  public function hash($page = null, $localize = true)
  {
    if (!$this->hash) {

      // Get folder and current page
      $folder = $this->app['config']->get('flatten::folder');
      if(!$page) $page = $this->app['request']->path();

      // Localize the cache or not
      if ($localize) {
        if(!starts_with($page, $this->lang)) $page = $this->lang.'/'.$page;
      }

      // Add prepend/append config options
      $prepend = $this->app['config']->get('flatten::prepend');
      $append  = $this->app['config']->get('flatten::append');
      if(is_array($prepend)) $prepend = implode('_', $prepend);
      if(is_array($append))  $append  = implode('_', $append);
      if($prepend) $page = $prepend.'_'.$page;
      if($append)  $page .= '_'.$append;

      // Slugify and prepend folder
      $page = str_replace('/', '_', $page);
      if($folder) $page = $folder . DS . $page;

      // Cache the current page to avoid repetition
      // $this->hash = $page;
      return $page;
    }

    return $this->hash;
  }

  /**
   * Whether the current page matches against an array of pages
   *
   * @param  array $ignored   An array of pages regex
   * @return boolean          Matches or not
   */
  private function matches($pages)
  {
    if(!$pages) return false;

    // Implode all pages into one single pattern
    $page = $this->hash;
    if(!$page) $page = $this->app['request']->path();
    $pages = implode('|', $pages);

    // Replace laravel patterns
    $pages = strtr($pages, $this->app['router']->patterns);

    return preg_match('#' .$pages. '#', $page);
  }

  /**
   * Render a content
   *
   * @param  string $content A content to render
   */
  public function render($content)
  {
    // Set correct header
    header('Content-Type: text/html; charset=utf-8');

    // Display content
    exit($content);
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////////// TESTERS ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Set the current URL for testing purposes
   *
   * @param string $hash The current hash to use
   */
  public function setHash($hash)
  {
    $this->hash = $hash;
  }
}
