<?php

namespace Touhidurabir\MetaFields\Tests;

use Config;
use Exception;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Touhidurabir\MetaFields\Models\MetaField;
use Touhidurabir\MetaFields\Tests\App\Models\User;
use Touhidurabir\MetaFields\Tests\App\Models\Profile;
use Touhidurabir\MetaFields\Tests\Traits\LaravelTestBootstrapping;


class ModelTest extends TestCase {

    use LaravelTestBootstrapping;

    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application $app
     * @return void
     */
    protected function defineEnvironment($app) {

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('app.url', 'http://localhost/');
        $app['config']->set('app.debug', false);
        $app['config']->set('app.key', env('APP_KEY', '1234567890123456'));
        $app['config']->set('app.cipher', 'AES-128-CBC');
    }


    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations() {

        include_once(__DIR__ . '/../database/migrations/create_meta_fields_table.php.stub');

        $this->loadMigrationsFrom(__DIR__ . '/App/database/migrations');
        
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        (new \CreateMetaFieldsTable)->up();

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback', ['--database' => 'testbench'])->run();
        });
    }


    /**
     * @test
     */
    public function the_model_can_be_initialsed() {

        $metaField = new MetaField;

        $this->assertIsObject($metaField);
        $this->assertInstanceOf(Model::class, $metaField);
    }


    /**
     * @test
     */
    public function the_model_will_point_to_proper_table() {

        $metaField = new MetaField;

        $this->assertEquals($metaField->getTable(), config('meta-field.table'));
    }


    /**
     * @test
     */
    public function the_proper_meta_cast_will_be_applied() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $meta = $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);
        
        $this->assertInstanceOf(Collection::class, $meta->metas);
        $this->assertEquals($meta->metas->first()->bio, 'some bio');
    }


    /**
     * @test
     */
    public function the_proper_meta_cast_that_changed_via_config_will_be_applied() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $meta = $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        Config::set('meta-field.meta_retrieve_cast', 'object');
        $this->assertEquals(config('meta-field.meta_retrieve_cast'), 'object');
        $this->assertIsObject($meta->metas);
        
        Config::set('meta-field.meta_retrieve_cast', 'array');
        $this->assertEquals(config('meta-field.meta_retrieve_cast'), 'array');
        $this->assertIsArray(($meta->refresh())->metas);

        Config::set('meta-field.meta_retrieve_cast', 'collection');
        $this->assertEquals(config('meta-field.meta_retrieve_cast'), 'collection');
        $this->assertInstanceOf(Collection::class, ($meta->refresh())->metas);
    }
}