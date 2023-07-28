<?php

use Services\IceRobokassaService;
use Illuminate\Support\ServiceProvider;

class RobokassaServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind('ice.robokassa', IceRobokassaService::class);
        $this->registerConfig();
        $this->registerRoutes();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfigs();
            $this->publishMigrations();
        }
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/robokassa.php', 'robokassa');
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/webhook_robokassa.php');
    }


    protected function publishMigrations(): void
    {
        if (!class_exists('CreateRobokassaTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_robokassa_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_robokassa_table.php'),
            ], 'migrations');
        }
    }

    protected function publishConfigs(): void
    {
        $this->publishes([
            __DIR__ . '/../config/robokassa.php' => config_path('robokassa.php'),
        ], 'config');
    }

}
