<?php

namespace Uzhlaravel\Maishapay\Commands;

use Illuminate\Console\Command;

class MaishapayCommand extends Command
{
    public $signature = 'maishapay';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
