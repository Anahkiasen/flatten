<?php
namespace Flatten\Facades;

use Flatten\FlattenServiceProvider;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for the templating-related methods
 */
class Template extends Facade
{
	/**
	 * Get the registered component.
	 *
	 * @return object
	 */
	protected static function getFacadeAccessor()
	{
		if (!static::$app) {
			static::$app = FlattenServiceProvider::make();
		}

		return 'flatten.templating';
	}
}
