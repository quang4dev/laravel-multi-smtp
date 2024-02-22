<?php

namespace Quang4dev\MultiSmtp;

use Illuminate\Support\ServiceProvider;
use Quang4dev\MultiSmtp\Console\InstallCommand;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            if (!class_exists('CreateEmailConfigsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/smtp_configs.stub' => database_path(
                        sprintf('migrations/%s_create_smtp_configs_table.php', date('Y_m_d_His'))
                    ),
                ], 'migrations');
            }
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            InstallCommand::class,
        ]);
    }
}
