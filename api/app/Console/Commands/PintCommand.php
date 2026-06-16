<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class PintCommand extends Command
{
    protected $signature = 'pint
                            {--test : 修正せずチェックのみ実行}
                            {--dirty : Gitで変更されたファイルのみを対象にする}';

    protected $description = 'Laravel Pintでコードスタイルを修正';

    public function handle(): int
    {
        $command = [base_path('vendor/bin/pint')];

        if ($this->option('test')) {
            $command[] = '--test';
        }

        if ($this->option('dirty')) {
            $command[] = '--dirty';
        }

        $this->info('Running Pint...');

        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->getExitCode();
    }
}
