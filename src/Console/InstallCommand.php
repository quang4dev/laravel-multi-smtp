<?php

namespace Quang4dev\MultiSmtp\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'laravel-multi-smtp:install';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Install all of the Email SMTP resources';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $this->comment('Publishing Migrations...');
        $this->callSilent('vendor:publish', ['--tag' => 'migrations']);

        $this->registerServiceProvider();

        $this->info('Email SMTP installed successfully.');
    }

    /**
     * Register the service provider in the application configuration file.
     *
     * @return void
     */
    protected function registerServiceProvider()
    {
        $namespace = Str::replaceLast('\\', '', app()->getNamespace());

        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, 'Quang4dev\\MultiSmtp\\EmailServiceProvider::class')) {
            return;
        }

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\EventServiceProvider::class," . PHP_EOL,
            "{$namespace}\\Providers\EventServiceProvider::class," . PHP_EOL . "        Quang4dev\MultiSmtp\EmailServiceProvider::class," . PHP_EOL,
            $appConfig
        ));
    }
}
