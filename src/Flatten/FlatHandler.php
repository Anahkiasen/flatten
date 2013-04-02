<?php
namespace Flatten;

class FlatHandler
{

  ////////////////////////////////////////////////////////////////////
  ///////////////////////// FLUSHING METHODS /////////////////////////
  ////////////////////////////////////////////////////////////////////

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
  public function flushAction($action, $parameters = array())
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
  public function flushRoute($route, $parameters = array())
  {
    // Get matching URL
    $url = route($route, $parameters);
    $url = $this->urlToPattern($url);

    // Flush any cache found with that pattern
    return $this->flush($url);
  }

}