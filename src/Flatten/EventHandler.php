<?php
namespace Flatten;

use Symfony\Component\HttpFoundation\Response;

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
		// Start buffer
		ob_start();

		// Render page if it's available in the cache
		if ($this->app['flatten.cache']->hasCache()) {
			return $this->app['flatten']->render();
		}
	}

	/**
	 * Save the current page in the cache
	 *
	 * @param Response|null $response
	 *
	 * @return false|null
	 */
	public function onApplicationDone(Response $response = null)
	{
		// Do not cache a Redirect Response or error pages
		if (
			!is_null($response) && (
				$response->isRedirection() ||
				$response->isNotFound() ||
				$response->isServerError() ||
				$response->isForbidden()
			)
		) {
			return false;
		}

		// Get the Response's or the buffer's contents
		$content = $response ? $response->getContent() : ob_end_flush();

		// Cache content
		$this->app['flatten.cache']->storeCache($content);
	}
}
