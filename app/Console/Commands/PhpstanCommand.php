<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class PhpstanCommand extends Command
{
    protected $signature = 'phpstan
                            {--level= : 解析レベル (0-9)}';

    protected $description = 'PHPStan静的解析を実行';

    public function handle(): int
    {
        $command = [
            base_path('vendor/bin/phpstan'),
            'analyse',
            '--memory-limit=2G',
        ];

        if ($this->option('level')) {
            $command[] = '--level='.$this->option('level');
        }

        $this->info('Running PHPStan...');

        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->getExitCode();
    }
}
