<?php

namespace Touhidurabir\MetaFields\Tests;

use Exception;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use Touhidurabir\MetaFields\Models\MetaField;
use Touhidurabir\MetaFields\Tests\App\Models\User;
use Touhidurabir\MetaFields\Tests\App\Models\Profile;
use Touhidurabir\MetaFields\Tests\Traits\LaravelTestBootstrapping;


class HasMetaTest extends TestCase {

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
    public function the_model_use_has_meta_will_have_metas_relation_exists() {

        $user = new User;
        
        $this->assertNotNull($user->metas);
        $this->assertInstanceOf(Collection::class, $user->metas);
        $this->assertInstanceOf(MorphMany::class, $user->metas());
    }


    /**
     * @test
     */
    public function the_storeMeta_will_push_meta_data_with_proper_relation_mapping() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $meta = $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        $this->assertInstanceOf(MetaField::class, $meta);
        $this->assertCount(1, $user->metas);
    }


    /**
     * @test
     */
    public function the_storeMeta_will_push_multiple_meta_data_if_allowed() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);
        $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        $this->assertCount(2, $user->metas);
    }


    /**
     * @test
     */
    public function the_storeMeta_will_not_push_multiple_meta_data_if_not_allowed() {

        $profile = Profile::create(['first_name' => 'first', 'last_name' => 'last']);
        $profile->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        $this->assertCount(1, $profile->metas);

        $profile->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        $this->assertCount(1, $profile->metas);
    }


    /**
     * @test
     */
    public function the_storeMeta_will_update_existing_one_on_multipe_call_if_multipe_not_allowed() {

        $profile = Profile::create(['first_name' => 'first', 'last_name' => 'last']);
        $profile->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);
        
        $meta = $profile->storeMeta(['bio' => 'update bio', 'dob' => '12-12-1990']);

        $this->assertEquals($meta->getRawOriginal('metas'), json_encode(['bio' => 'update bio', 'dob' => '12-12-1990']));
    }


    /**
     * @test
     */
    public function the_updateMeta_will_throw_exception_if_multiple_allowed() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        $this->expectException(Exception::class);
        $user->updateMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);
    }


    /**
     * @test
     */
    public function the_updateMeta_will_update_or_create_if_multipe_now_allowed() {

        $profile = Profile::create(['first_name' => 'first', 'last_name' => 'last']);
        $profile->updateMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);
        
        $this->assertCount(1, $profile->metas);

        $meta = $profile->updateMeta(['bio' => 'updated bio', 'dob' => '12-12-1990']);

        $this->assertCount(1, $profile->metas);

        $this->assertEquals($meta->getRawOriginal('metas'), json_encode(['bio' => 'updated bio', 'dob' => '12-12-1990']));
    }


    /**
     * @test
     */
    public function the_deleteMeta_will_delete_meta_as_soft_delete() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);
        $user->deleteMeta();
        
        $this->assertCount(0, $user->metas);
        $this->assertCount(1, $user->metas()->withTrashed()->get());
    }


    /**
     * @test
     */
    public function the_deleteMeta_will_force_delete_metas_if_instructed_so() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);
        $user->deleteMeta(true);
        
        $this->assertCount(0, $user->metas);
        $this->assertCount(0, $user->metas()->withTrashed()->get());
    }

    
    /**
     * @test
     */
    public function parent_model_delete_force_delete_and_restore_will_refelect_on_metas() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $meta = $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        $user->delete();
        $this->assertNotNull($meta->refresh()->deleted_at);

        $user->restore();
        $this->assertNull($meta->refresh()->deleted_at);

        $user->forceDelete();
        $this->assertNull(MetaField::withTrashed()->find($meta->id));
    }


    /**
     * @test
     */
    public function meta_record_will_not_sync_to_parent_model_state_if_instructed_so() {

        $profile = Profile::create(['first_name' => 'first', 'last_name' => 'last']);
        $meta = $profile->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        $profile->delete();
        $this->assertNotNull($meta->refresh());
        $this->assertNull($meta->deleted_at);
    }


    /**
     * @test
     */
    public function the_method_hasMeta_will_provide_if_parent_has_metas_associated() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $meta = $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        $this->assertTrue($user->hasMeta());

        $this->assertFalse((new Profile)->hasMeta());
    }
    

    /**
     * @test
     */
    public function the_method_isMetaAssociated_will_return_bool_if_given_meta_field_association_found() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $meta = $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        $this->assertTrue($user->isMetaAssociated('bio'));
        $this->assertFalse($user->isMetaAssociated('name'));
        $this->assertFalse((new Profile)->isMetaAssociated('bio'));
    }


    /**
     * @test
     */
    public function the_method_isMetaAssociated_will_return_model_collection_if_given_meta_field_association_found() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $meta = $user->storeMeta(['bio' => 'some bio', 'dob' => '12-12-1990']);

        $this->assertInstanceOf(Collection::class, $user->isMetaAssociated('bio', false));
        $this->assertCount(1, $user->isMetaAssociated('bio', false));
        $this->assertCount(0, (new Profile)->isMetaAssociated('bio', false));
    }


    /**
     * @test
     */
    public function the_method_metaValue_will_extrat_meta_field_value() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $user->storeMeta(['bio' => 'bio', 'dob' => '12-12-1990']);
        
        $this->assertEquals($user->metaValue('bio'), 'bio');
        $this->assertNull($user->metaValue('name'));
    }


    /**
     * @test
     */
    public function the_method_metaValue_will_extrat_all_meta_field_values_if_instructed_so() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $user->storeMeta(['bio' => 'bio', 'dob' => '12-12-1990']);
        $user->storeMeta(['bio' => 'new bio', 'dob' => '12-12-1990']);

        $this->assertCount(2, $user->metaValue('bio', true));
        $this->assertEquals($user->metaValue('bio', true), ['bio', 'new bio']);
    }


    /**
     * @test
     */
    public function the_method_metaValue_will_return_null_if_none_found() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $user->storeMeta(['bio' => 'bio', 'dob' => '12-12-1990']);

        $this->assertNull($user->metaValue('name'));
    }


    /**
     * @test
     */
    public function the_method_metaValue_will_return_empty_array_if_none_found_for_multiple_instructed() {

        $user = User::create(['email' => 'testuser1@test.com', 'password' => '123456']);
        $user->storeMeta(['bio' => 'bio', 'dob' => '12-12-1990']);
        $user->storeMeta(['bio' => 'new bio', 'dob' => '12-12-1990']);

        $this->assertEmpty($user->metaValue('name'));
    }

}