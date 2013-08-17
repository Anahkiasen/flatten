<?php
namespace Flatten;

use Illuminate\Container\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gets informations about the current page and delegates them
 * to the other classes
 */
class Flatten
{
  /**
   * The IoC Container
   *
   * @var Container
   */
  protected $app;

  /**
   * Setup Flatten and hook it to the application
   *
   * @param Container $app
   */
  public function __construct(Container $app)
  {
    $this->app = $app;
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////// CACHING PROCESS //////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Starts the caching system
   *
   * @return boolean
   */
  public function start()
  {
    if ($this->shouldRun()) {
      $this->app['flatten.events']->onApplicationBoot();
    }
  }

  /**
   * Stops the caching system
   *
   * @return boolean
   */
  public function end()
  {
    if ($this->shouldRun()) {
      return $this->app['flatten.events']->onApplicationDone();
    }
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////////// CHECKS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Hook Flatten to Laravel's events
   *
   * @return boolean Whether Flatten started caching or not
   */
  public function shouldRun()
  {
    // If we're in the console or in a disallowed environment
    if (!$this->isInAllowedEnvironment()) {
      return false;
    }

    return $this->shouldCachePage();
  }

  /**
   * Whether the current page is authorized to be cached
   *
   * @return boolean
   */
  public function shouldCachePage()
  {
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

    return (bool) $cache;
  }

  /**
   * Check if the current environment is allowed to be cached
   *
   * @return boolean
   */
  protected function isInAllowedEnvironment()
  {
    if(!$this->app->bound('env')) {
      return true;
    }

    // Get allowed environments
    $allowedEnvs = (array) $this->app['config']->get('flatten::environments');

    return !$this->app->runningInConsole() and !in_array($this->app['env'], $allowedEnvs);
  }

  /**
   * Whether the current page matches against an array of pages
   *
   * @param array $pages An array of pages to match against
   *
   * @return boolean
   */
  protected function matches($pages)
  {
    // Implode all pages into one single pattern
    $page = $this->app['flatten.cache']->getHash();
    if(!$page) {
      $page = $this->getCurrentUrl();
    }

    return preg_match('#' .implode('|', $pages). '#', $page);
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// RENDERING ///////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Render a content
   *
   * @param  string $content A content to render
   */
  public function render($content = null)
  {
    // If no content, get from cache
    if (!$content) {
      $content = $this->app['flatten.cache']->getCache();
    }

    // Else, send the response with the content
    if ($content) {
      $response = new Response($content, 200);
      $response->send();

      exit();
    }
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////////// HELPERS ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get the current page URL
   *
   * @return string
   */
  protected function getCurrentUrl()
  {
    $pattern = trim($this->app['request']->getPathInfo(), '/');

    return $pattern == '' ? '/' : $pattern;
  }

  /**
   * Get the current page's hash
   *
   * @return string A page hash
   */
  public function computeHash($page = null)
  {
    // Get current page URI
    if (!$page) {
      $page = $this->getCurrentUrl();
    }

    // Add additional salts
    $salts = $this->app['config']->get('flatten::saltshaker');
    foreach ($salts as $salt) $page .= $salt;
    $salts[] = $this->app['request']->getMethod();
    $salts[] = $page;

    return implode('-', $salts);
  }
}
