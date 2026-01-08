<?php

namespace Digood\Sso;

use Illuminate\Support\ServiceProvider;

class SsoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 发布配置文件（可选）
        $this->publishes([
            __DIR__ . '/../config/sso.php' => config_path('sso.php'),
        ], 'digood-sso-config');

        // 加载路由
        $this->loadRoutesFrom(__DIR__ . '/../routes/sso.php');

        // 视图
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'digood.sso');

        // 迁移
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        // 合并配置
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sso.php',
            'sso'
        );

        // 绑定服务（可选）
        $this->app->singleton('digood-sso.service', function ($app) {
            return new Services\SsoService();
        });
    }

}