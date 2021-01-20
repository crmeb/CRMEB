<?php

namespace Songshenzong\Support;

use Illuminate\Support\ServiceProvider;

/**
 * Class StringsServiceProvider
 *
 * @package Songshenzong\Support
 */
class StringsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'Strings',
            static function () {
                return new Strings();
            }
        );

        $this->app->alias('Strings', StringsFacade::class);
    }
}
