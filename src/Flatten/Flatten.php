<?php
namespace Flatten;

use Illuminate\Http\Response;
use Illuminate\Support\Str;

class Flatten
{
  /**
   * The current URL
   *
   * @var string
   */
  protected $hash = null;

  /**
   * The current language
   *
   * @var string
   */
  protected $lang = null;

  /**
   * Setup Flatten and hook it to the application
   *
   * @param Illuminate\Container\Container $app
   */
  public function __construct($app)
  {
    $this->app  = $app;
    $this->hash = $this->computeHash();
  }

  /**
   * Render a content
   *
   * @param  string $content A content to render
   */
  public function render($content)
  {
    $response = new Response($content, 200);
    $response->send();

    exit();
  }

  /**
   * Hook Flatten to Laravel's events
   *
   * @return boolean Whether Flatten started caching or not
   */
  public function shouldCache()
  {
    // If we're in the console or in a disallowed environment
    if($this->app->runningInConsole() or
      !$this->isInAllowedEnvironment()) {
      return false;
    }

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

    return (bool) $cache;
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
    return true;
    // Get allowed environments
    $applicationEnvironment = $this->app['env'];
    $allowedEnvironnements  = (array) $this->app['config']->get('flatten::environments');

    return !in_array($applicationEnvironment, $allowedEnvironnements);
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
   * Whether the current page matches against an array of pages
   *
   * @param array $pages An array of pages to match against
   *
   * @return boolean
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

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// PAGE HASH ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get the current page's hash
   *
   * @return string A page hash
   */
  protected function computeHash($page = null, $localize = true)
  {
    // Get current page URI
    if(!$page) {
      $page = $this->app['request']->path();
    }

    // Localize the cache or not
    if ($localize and !Str::startsWith($page, $this->lang)) {
      $page = $this->lang. '/' .$page;
    }

    // Add additional salts
    $salts = $this->app['config']->get('flatten::saltshaker');
    foreach ($salts as $salt) $page .= $salt;

    return $page;
  }

  /**
   * Get the URL hash
   *
   * @return string
   */
  public function getHash()
  {
    return $this->hash;
  }

}
