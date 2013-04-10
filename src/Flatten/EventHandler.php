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
    // Start buffer
    ob_start();

    // Render page if it's available in the cache
    if ($this->app['flatten.cache']->hasCache()) {
      return $this->app['flatten']->render();
    }
  }

  /**
   * Save the current page in the cache
   */
  public function onApplicationDone()
  {
    // Get content from buffer
    $content = ob_get_contents();
    if (!$content) return false;

    // Cache page forever or for X minutes
    $this->app['flatten.cache']->storeCache($content);

    // Render page
    $this->app['flatten']->render($content);
  }
}