<?php
namespace Flatten;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the rendering of responses and starting of events
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

	/**
	 * Delegate flushing actions to CacheHandler
	 *
	 * @param  string $method
	 * @param  array $arguments
	 *
	 * @return mixed
	 */
	public function __call($method, $arguments)
	{
		$class = $this;

		// Go through the class Flatten decorates
		$decorators = array('cache', 'templating');
		foreach ($decorators as $decorator) {
			$decorator = $this->app['flatten.'.$decorator];
			if (method_exists($decorator, $method)) {
				$class = $decorator;
				break;
			}
		}

		return call_user_func_array(array($decorator, $method), $arguments);
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////// CACHING PROCESS //////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Starts the caching system
	 *
	 * @return boolean
	 *
	 * @codeCoverageIgnore
	 */
	public function start()
	{
		if ($this->app['flatten.context']->shouldRun()) {
			return $this->app['flatten.events']->onApplicationBoot();
		}
	}

	/**
	 * Stops the caching system
	 *
	 * @param Response $response A response to render on end
	 *
	 * @return boolean
	 *
	 * @codeCoverageIgnore
	 */
	public function end($response = null)
	{
		if ($this->app['flatten.context']->shouldRun()) {
			return $this->app['flatten.events']->onApplicationDone($response);
		}
	}

	/**
	 * Kickstart a raw version of Flatten
	 *
	 * @return string|void
	 */
	public static function kickstart()
	{
		// Get the salts
		$salts = func_get_args();
		$salts = $salts ? join('-', $salts).'-' : null;

		// Get storage path
		$paths   = __DIR__.'/../../../../../bootstrap/paths.php';
		if (file_exists($paths)) {
			$storage = include $paths;
			$storage = $storage['storage'];
		} else {
			$storage =__DIR__.'/../../storage';
		}

		// Compute the cache path and display it if it exists
		if(isset($_SERVER['REQUEST_URI']) and $_SERVER['REQUEST_METHOD'] === 'GET') {
			$key      = $salts.'GET-'.$_SERVER['REQUEST_URI'];
			$parts    = array_slice(str_split($hash = md5($key), 2), 0, 2);
			$filename = $storage.'/cache/'.join('/', $parts).'/'.$hash;

			if(file_exists($filename)) {
				$contents = file_get_contents($filename);
				exit(unserialize(substr($contents, 10)));
			}
		}
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// RENDERING ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Create a response to send from content
	 *
	 * @param  string $content
	 *
	 * @return Response
	 */
	public function getResponse($content = null)
	{
		// If no content, get from cache
		if (!$content) {
			$content = $this->app['flatten.cache']->getCache();
		}

		return new Response($content);
	}

	/**
	 * Render a content
	 *
	 * @param  string $content A content to render
	 *
	 * @codeCoverageIgnore
	 */
	public function render($content = null)
	{
		$this->getResponse($content)->send();

		exit;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the current page's hash
	 *
	 * @return string A page hash
	 */
	public function computeHash($page = null)
	{
		// Get current page URI
		if (!$page) {
			$page = $this->app['flatten.context']->getCurrentUrl();
		}

		// Add additional salts
		$salts = $this->app['config']->get('flatten::saltshaker');

		// Add method and page
		$salts[] = $this->app['request']->getMethod();
		$salts[] = $page;

		return implode('-', $salts);
	}
}
