<?php

namespace Touhidurabir\MetaFields\Tests;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Touhidurabir\MetaFields\Models\MetaField;
use Touhidurabir\MetaFields\Tests\App\Models\User;
use Touhidurabir\MetaFields\Tests\App\Models\Profile;
use Touhidurabir\MetaFields\Tests\App\FormRequests\StoreUser;
use Touhidurabir\MetaFields\Tests\Traits\LaravelTestBootstrapping;


class WithMetaFieldsTest extends TestCase {

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
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router) {
        
        Route::post('/users', function(StoreUser $request) {
            
            return response()->json($request->metas());

        })->middleware(['web']);
    }

    
    /**
     * @test
     */
    public function the_validation_will_failed_with_invalid_meta_data() {

        $this
            ->postJson("/users", [
                'email' => 'testmail1@test.com',
                'password' => '123456'
            ])
            ->assertStatus(422);

        $this
            ->postJson("/users", [
                'email' => 'testmail1@test.com',
                'password' => '123456',
                'bio' => [
                    'a testing',
                ]
            ])
            ->assertStatus(422);
    }


    /**
     * @test
     */
    public function the_post_request_will_pass_with_proper_meta_data() {

        $this->postJson("/users", [
            'email' => 'testmail1@test.com',
            'password' => '123456',
            'bio' => 'some bio'
        ])->assertStatus(200);
    }


    /**
     * @test
     */
    public function the_metas_can_be_retrived_from_request() {

        $this
            ->postJson("/users", [
                'email' => 'testmail1@test.com',
                'password' => '123456',
                'bio' => 'some bio'
            ])
            ->assertStatus(200)
            ->assertJson([
                'bio' => 'some bio'
            ]);

        $this
            ->postJson("/users", [
                'email' => 'testmail1@test.com',
                'password' => '123456',
                'bio' => 'some bio',
                'dob' => '1999-12-12'
            ])
            ->assertStatus(200)
            ->assertJson([
                'bio' => 'some bio',
                'dob' => '1999-12-12'
            ]);
    }

    
    /**
     * @test
     */
    public function it_will_omit_those_that_are_not_defined_in_meta_rules() {

        $this
            ->postJson("/users", [
                'email' => 'testmail1@test.com',
                'password' => '123456',
                'bio' => 'some bio',
                'dob' => '1999-12-12',
                'details' => 'some more details'
            ])
            ->assertStatus(200)
            ->assertExactJson([
                'bio' => 'some bio',
                'dob' => '1999-12-12'
            ]);
    }

}