<?php

namespace Touhidurabir\MetaFields\Tests\Traits;

use Touhidurabir\MetaFields\MetaFieldsServiceProvider;

trait LaravelTestBootstrapping {

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app) {

        return [
            MetaFieldsServiceProvider::class,
        ];
    }
       
}