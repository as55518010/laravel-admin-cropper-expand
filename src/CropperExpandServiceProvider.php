<?php

namespace Encore\CropperExpand;

use Encore\Admin\Admin;
use Encore\Admin\Form;
use Illuminate\Support\ServiceProvider;

class CropperExpandServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(CropperExpand $extension)
    {
        if (! CropperExpand::boot()) {
            return ;
        }

        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'laravel-admin-cropper-expand');
        }

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes(
                [$assets => public_path('vendor/laravel-admin-ext/cropper-expand')],
                'laravel-admin-cropper-expand'
            );
            $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'laravel-admin-cropper-expand-lang');
        }
        Admin::booting(function () {
            Form::extend('cropperExpand', CropperExpandController::class);
        });
    }
}
