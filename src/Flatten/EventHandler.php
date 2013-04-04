<?php
namespace Flatten;

class EventHandler
{

  /**
   * The IoC Container
   *
   * @var Container
   */
  protected $app;

  /**
   * Bind the application to the event handler
   *
   * @param Container $app
   */
  public function __construct($app)
  {
    $this->app = $app;
  }

  /**
   * Load a page from the cache
   */
  public function onApplicationBoot()
  {
    // Get page from cache if any
    $hash  = $this->app['flatten']->getHash();
    $cache = $this->app['cache']->get($hash);

    // Start buffer
    ob_start();

    // Render page if it's available in the cache
    if ($cache) {
      return $this->app['flatten']->render($cache);
    }
  }

  /**
   * Save the current page in the cache
   */
  public function onApplicationDone()
  {
    // Get static variables
    $hash = $this->app['flatten']->getHash();
    $cachetime = $this->app['config']->get('flatten::cachetime');

    // Get content from buffer
    $content = ob_get_clean();

    // Cache page forever or for X minutes
    if($cachetime == 0) $this->app['cache']->forever($hash, $content);
    else $this->app['cache']->remember($hash, $cachetime, $content);

    // Render page
    $this->app['flatten']->render($content);
  }
}