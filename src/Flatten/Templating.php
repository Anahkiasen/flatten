<?php
namespace Flatten;

use Closure;
use Illuminate\Container\Container;

/**
 * Handles hooking into the various templating systems
 */
class Templating
{
	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Build a new Templating
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// ENGINES ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Register the templating tags with the various engines
	 *
	 * @return void
	 */
	public function registerTags()
	{
		$this->registerBlade();
	}

	/**
	 * Register the Flatten tags with Blade
	 *
	 * @return void
	 */
	public function registerBlade()
	{
		// Extend Blade
		$blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();
		$blade->extend(function($view, $blade) {

			// Replace opener
			$pattern = $blade->createMatcher('cache');
			$replace = "<?php echo Flatten\Facades\Template::section('$1', function() { ?>";
			$view    = preg_replace($pattern, $replace, $view);

			// Replace closing tag
			$view = str_replace('@endcache', '<?php }); ?>', $view);

			return $view;
		});
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// SECTIONS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Register a section to cache with Flatten
	 *
	 * @param  string  $name
	 * @param  Closure $contents
	 *
	 * @return void
	 */
	public function section($name, Closure $contents)
	{
		$lifetime = $this->app['flatten.cache']->getLifetime();

		return $this->app['cache']->remember($name, $lifetime, function() use ($contents) {
			ob_start();
				print $contents();
			return ob_get_clean();
		});
	}
}