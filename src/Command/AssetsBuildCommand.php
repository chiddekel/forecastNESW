<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'assets:build',
    description: 'Build CSS assets with Tailwind',
)]
class AssetsBuildCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch mode (auto-rebuild)')
            ->addOption('prod', 'p', InputOption::VALUE_NONE, 'Production build (minified + asset-map)')
            ->addOption('no-copy', null, InputOption::VALUE_NONE, 'Skip copying to public/')
            ->addOption('no-cache-clear', null, InputOption::VALUE_NONE, 'Skip clearing Symfony cache')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $watch = $input->getOption('watch');
        $prod = $input->getOption('prod');
        $noCopy = $input->getOption('no-copy');
        $noCacheClear = $input->getOption('no-cache-clear');

        $io->title('🎨 Building CSS Assets');

        // Build Tailwind CSS
        if ($watch) {
            $io->section('Watch mode - CSS will rebuild automatically');
            $process = new Process([
                'npx', 'tailwindcss',
                '-i', './assets/styles/app.css',
                '-o', './assets/styles/output.css',
                '--watch'
            ]);
        } else {
            $io->section('Building Tailwind CSS...');
            $minify = $prod ? ['--minify'] : [];
            $process = new Process(array_merge([
                'npx', 'tailwindcss',
                '-i', './assets/styles/app.css',
                '-o', './assets/styles/output.css'
            ], $minify));
        }

        $process->setTimeout(null);
        $process->run(function ($type, $buffer) use ($io) {
            $io->write($buffer);
        });

        if (!$process->isSuccessful() && !$watch) {
            $io->error('Tailwind CSS build failed');
            return Command::FAILURE;
        }

        if ($watch) {
            // Watch mode runs indefinitely
            return Command::SUCCESS;
        }

        $io->success('Tailwind CSS built successfully');

        // Copy to public
        if (!$noCopy) {
            $io->section('Copying CSS to public/styles/...');
            
            if (!is_dir('public/styles')) {
                mkdir('public/styles', 0755, true);
            }
            
            copy('assets/styles/output.css', 'public/styles/output.css');
            $io->success('CSS copied to public/styles/output.css');
        }

        // Clear Symfony cache (important for production!)
        if (!$noCacheClear) {
            $io->section('Clearing Symfony cache...');
            
            $env = $prod ? 'prod' : 'dev';
            $process = new Process([
                'php', 'bin/console', 'cache:clear', '--env=' . $env, '--no-warmup'
            ]);
            
            $process->run(function ($type, $buffer) use ($io) {
                $io->write($buffer);
            });

            if (!$process->isSuccessful()) {
                $io->warning('Cache clear failed, but continuing...');
            } else {
                $io->success('Cache cleared for ' . $env . ' environment');
            }

            // Clear OPcache (important for templates!)
            if (function_exists('opcache_reset')) {
                if (opcache_reset()) {
                    $io->success('OPcache cleared');
                } else {
                    $io->warning('OPcache reset failed (might need server restart)');
                }
            }

            // Restart Symfony server to apply all changes
            $io->section('Restarting Symfony server...');
            $restartProcess = new Process(['symfony', 'server:stop']);
            $restartProcess->run();
            
            $startProcess = new Process(['symfony', 'server:start', '-d']);
            $startProcess->run();
            
            if ($startProcess->isSuccessful()) {
                $io->success('Server restarted successfully');
            } else {
                $io->warning('Server restart failed - you may need to restart manually');
            }
        }

        // Production: compile Asset Mapper
        if ($prod) {
            $io->section('Compiling Asset Mapper...');
            
            $process = new Process([
                'php', 'bin/console', 'asset-map:compile', '--env=prod'
            ]);
            
            $process->run(function ($type, $buffer) use ($io) {
                $io->write($buffer);
            });

            if (!$process->isSuccessful()) {
                $io->error('Asset Mapper compilation failed');
                return Command::FAILURE;
            }

            $io->success('Asset Mapper compiled successfully');
        }

        // Final summary
        $io->newLine();
        $io->writeln('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $io->writeln('✅ Build completed successfully!');
        $io->writeln('');
        $io->writeln('📋 What was done:');
        $io->writeln('   ✅ Tailwind CSS built' . ($prod ? ' (minified)' : ''));
        if (!$noCopy) {
            $io->writeln('   ✅ CSS copied to public/styles/');
        }
        if (!$noCacheClear) {
            $io->writeln('   ✅ Symfony cache cleared (' . ($prod ? 'prod' : 'dev') . ')');
            if (function_exists('opcache_reset')) {
                $io->writeln('   ✅ OPcache cleared');
            }
            $io->writeln('   ✅ Server restarted');
        }
        if ($prod) {
            $io->writeln('   ✅ Asset Mapper compiled');
        }
        $io->writeln('');
        
        if ($prod) {
            $io->writeln('📦 Mode: Production');
            $io->writeln('🚀 Ready for deployment!');
        } else {
            $io->writeln('📦 Mode: Development');
            $io->writeln('💡 Tip: Use --prod for production build');
        }
        
        $io->writeln('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return Command::SUCCESS;
    }
}

