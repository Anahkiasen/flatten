<?php
namespace Flatten;

use \Illuminate\Events\Subscriber;

class EventHandler extends Subscriber
{
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
  public function onApplicationBoot($event)
  {
    // Get page from cache if any
    $hash = $this->app['flatten']->hash();
    $cache = $this->app['cache']->get($hash);

    // Render page
    if ($cache) return $this->app['flatten']->render($cache);
  }

  /**
   * Save the current page in the cache
   */
  private function save()
  {
    // Get static variables
    $hash = $this->hash();
    $cachetime = $this->app['config']->get('flatten::cachetime');

    $this->app['events']->listen('laravel.done', function() use ($cachetime, $hash) {

      // Get content from buffer
      $content = ob_get_clean();

      // Cache page forever or for X minutes
      if($cachetime == 0) $this->app['cache']->forever($hash, $content);
      else $this->app['cache']->remember($hash, $content, $cachetime);

      // Render page
      \Flatten\Flatten::render($content);
    });
  }

  public static function subscribes()
  {
    return array(
      'application.boot' => array(
        array('onApplicationBoot')
      ),
    );
  }
}