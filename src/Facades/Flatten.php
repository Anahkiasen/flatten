<?php

namespace Flatten\Facades;

use Flatten\FlattenServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

/**
 * Flatten facade for the Laravel framework.
 */
class Flatten extends Facade
{
    /**
     * Get the registered component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$app) {
            static::$app = new Container();
            $provider = new FlattenServiceProvider(static::$app);
            $provider->register();
        }

        return 'flatten';
    }
}
