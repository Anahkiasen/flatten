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
		if ($this->shouldRun()) {
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
		if ($this->shouldRun()) {
			return $this->app['flatten.events']->onApplicationDone($response);
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

		// Else, send the response with the content
		if ($content) {
			return new Response($content, 200);
		}

		return new Response;
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
