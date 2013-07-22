<?php
namespace Flatten;

/**
 * Hooks into the main events to execute actions
 */
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
   *
   * @return  string|null
   */
  public function onApplicationBoot()
  {
    ob_start();

    // Render page if it's available in the cache
    if ($this->app['flatten.cache']->hasCache()) {
      return $this->app['flatten']->render();
    }
  }

  /**
   * Save the current page in the cache
   *
   * @return void
   */
  public function onApplicationDone()
  {
    // Get content from buffer
    $content = ob_get_flush();

    // Cache content
    $this->app['flatten.cache']->storeCache($content);
  }
}
