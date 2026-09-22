<?php

namespace Wsmallnews\Order\Commands;

use Wsmallnews\Support\Commands\PackageInstallCommand;

class OrderInstallCommand extends PackageInstallCommand
{
    protected string $packageName = 'sn-order';
}
