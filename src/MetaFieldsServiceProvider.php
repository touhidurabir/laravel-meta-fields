<?php

namespace Touhidurabir\MetaFields;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class MetaFieldsServiceProvider extends ServiceProvider {
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {

        $this->publishes([
            __DIR__.'/../config/meta-field.php' => base_path('config/meta-field.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_meta_fields_table.php.stub' => $this->getMigrationFileName('create_meta_fields_table.php'),
        ], 'migrations');
    }

    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {

        $this->mergeConfigFrom(
            __DIR__.'/../config/meta-field.php', 'meta-field'
        );

    }


    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param  string $migrationFileName
     * @return string
     */
    protected function getMigrationFileName($migrationFileName): string {
        
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path . '*_' . $migrationFileName);
            })
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }

}