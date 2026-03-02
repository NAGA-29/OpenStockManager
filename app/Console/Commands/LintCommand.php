<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class LintCommand extends Command
{
    protected $signature = 'lint
                            {--fix : Pintによるコードスタイル自動修正も実行}
                            {--dirty : Gitで変更されたファイルのみを対象にする}';

    protected $description = 'PintのチェックとPHPStan静的解析を一括実行';

    public function handle(): int
    {
        $this->info('=== Pint (コードスタイルチェック) ===');
        $pintExitCode = $this->runPint();

        $this->newLine();
        $this->info('=== PHPStan (静的解析) ===');
        $phpstanExitCode = $this->runPhpstan();

        $this->newLine();
        if ($pintExitCode === 0 && $phpstanExitCode === 0) {
            $this->info('All checks passed!');

            return self::SUCCESS;
        }

        $this->error('Some checks failed.');

        return self::FAILURE;
    }

    private function runPint(): int
    {
        $command = [base_path('vendor/bin/pint')];

        if (! $this->option('fix')) {
            $command[] = '--test';
        }

        if ($this->option('dirty')) {
            $command[] = '--dirty';
        }

        return $this->runProcess($command);
    }

    private function runPhpstan(): int
    {
        $command = [
            base_path('vendor/bin/phpstan'),
            'analyse',
            '--memory-limit=2G',
        ];

        return $this->runProcess($command);
    }

    private function runProcess(array $command): int
    {
        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->getExitCode();
    }
}
