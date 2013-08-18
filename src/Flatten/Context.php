<?php
namespace Flatten;

use Illuminate\Container\Container;

/**
 * Provides informations about the current context
 */
class Context
{
	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Whether Flatten is run in CLI
	 *
	 * @var boolean
	 */
	protected $inConsole = false;

	/**
	 * Build a new Context
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
		$this->inConsole = php_sapi_name() == 'cli';
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
	public function isInAllowedEnvironment()
	{
		if (!$this->app->bound('env')) {
			return true;
		}

		// Get allowed environments
		$allowedEnvs = (array) $this->app['config']->get('flatten::environments');

		return !$this->inConsole and !in_array($this->app['env'], $allowedEnvs);
	}

	/**
	 * Whether the current page matches against an array of pages
	 *
	 * @param array $pages An array of pages to match against
	 *
	 * @return boolean
	 */
	public function matches($pages)
	{
		// Implode all pages into one single pattern
		$page    = $this->getCurrentUrl();
		$pattern = '#' .implode('|', $pages). '#';

		return (bool) preg_match($pattern, $page);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Change the inConsole variable
	 *
	 * @param  boolean $inConsole
	 *
	 * @return void
	 */
	public function inConsole($inConsole = false)
	{
		$this->inConsole = $inConsole;
	}

	/**
	 * Get the current page URL
	 *
	 * @return string
	 */
	public function getCurrentUrl()
	{
		return '/'.ltrim($this->app['request']->path(), '/');
	}
}