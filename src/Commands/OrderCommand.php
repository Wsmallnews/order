<?php

namespace Wsmallnews\Order\Commands;

use Illuminate\Console\Command;

class OrderCommand extends Command
{
    public $signature = 'order';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
