<?php

namespace Wsmallnews\Order\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Wsmallnews\Order\Features\Shortcuts\ShortcutRegistry
 */
class ShortcutRegistry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wsmallnews\Order\Features\Shortcuts\ShortcutRegistry::class;
    }
}
