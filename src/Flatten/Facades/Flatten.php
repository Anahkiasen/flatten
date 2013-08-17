<?php
namespace Flatten\Facades;

use Flatten\FlattenServiceProvider;
use Illuminate\Support\Facades\Facade;

/**
 * Flatten facade for the Laravel framework
 */
class Flatten extends Facade
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

		return 'flatten';
	}
}
