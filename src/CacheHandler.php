<?php

namespace Flatten;

use Illuminate\Container\Container;

/**
 * Handles the caches of the various pages.
 */
class CacheHandler
{
    /**
     * The current cache hash.
     *
     * @var string
     */
    protected $hash;

    /**
     * The IoC Container.
     *
     * @var Container
     */
    protected $app;

    /**
     * Build a new CacheHandler.
     *
     * @param Container $app
     * @param string    $hash
     */
    public function __construct(Container $app, $hash)
    {
        $this->app = $app;
        $this->hash = $hash;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////// CURRENT HASH ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Returns the current page in cache.
     *
     * @return string
     */
    public function getCache()
    {
        return $this->app['cache']->get($this->hash);
    }

    /**
     * Check if Flatten has a cache of the current page.
     *
     * @return bool
     */
    public function hasCache()
    {
        return $this->app['cache']->has($this->hash);
    }

    /**
     * Store contents in the cache.
     *
     * @param string $content The content to store
     *
     * @return bool
     */
    public function storeCache($content)
    {
        if (!$content) {
            return false;
        }

        // Log caching
        if ($this->app->bound('log')) {
            $this->app['log']->info('Caching page '.$this->hash);
        }

        // Add page to cached pages
        $cached = array_merge($this->getCachedPages(), [$this->hash]);
        $this->app['flatten.storage']->set('cached', $cached);

        // Add timestamp to cache
        $content .= PHP_EOL.'<!-- cached on '.date('Y-m-d H:i:s').' -->';

        return $this->app['cache']->put(
            $this->hash, $content, $this->getLifetime()
        );
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// FLUSHERS ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Flush a specific pattern.
     *
     * @param string|null $pattern
     */
    public function flushPattern($pattern = null)
    {
        // Get pages in cache
        $pages = $cached = (array) $this->app['flatten.storage']->get('cached');

        // Flush them if they match
        foreach ($pages as $key => $page) {
            if (!$pattern || preg_match($pattern, $page)) {
                $this->app['cache']->forget($page);
                unset($cached[$key]);
            }
        }

        $this->app['flatten.storage']->set('cached', $cached);
    }

    /**
     * Flush all pages.
     */
    public function flushAll()
    {
        return $this->flushPattern();
    }

    /**
     * Flush an URL.
     *
     * @param string $url
     */
    public function flushUrl($url)
    {
        return $this->flushPattern($this->urlToPattern($url));
    }

    /**
     * Flush an action.
     *
     * @param string $action
     * @param array  $parameters
     */
    public function flushAction($action, $parameters = [])
    {
        $url = $this->app['url']->action($action, $parameters);

        return $this->flushUrl($url);
    }

    /**
     * Flush a route.
     *
     * @param string $route
     * @param array  $parameters
     */
    public function flushRoute($route, $parameters = [])
    {
        $url = $this->app['url']->route($route, $parameters);

        return $this->flushUrl($url);
    }

    /**
     * Transforms an URL into a pattern.
     *
     * @param string $url
     *
     * @return string
     */
    protected function urlToPattern($url)
    {
        // Replace root in URL
        $url = str_replace($this->app['request']->root().'/', null, $url);

        return '#'.$url.'#';
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////// CACHE META ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get an array of cached pages.
     *
     * @return array
     */
    public function getCachedPages()
    {
        return (array) $this->app['flatten.storage']->get('cached');
    }

    /**
     * Return the configured cache lifetime.
     *
     * @return int
     */
    public function getLifetime()
    {
        return (int) $this->app['config']->get('flatten::lifetime') ?: 999 * 999;
    }

    /**
     * Get the URL hash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
