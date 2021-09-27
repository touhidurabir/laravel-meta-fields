<?php

namespace Touhidurabir\MetaFields\Tests;

use Exception;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Touhidurabir\MetaFields\Tests\App\Models\User;
use Touhidurabir\MetaFields\Tests\App\Models\Profile;
use Touhidurabir\MetaFields\Tests\Traits\LaravelTestBootstrapping;


class DatabaseTest extends TestCase {

    use LaravelTestBootstrapping;
    
    /**
     * @test
     */
    public function the_meta_field_table_in_config_will_run_via_migration() {

        include_once(__DIR__ . '/../database/migrations/create_meta_fields_table.php.stub');

        $this->assertNull((new \CreateMetaFieldsTable)->up());

        $this->assertTrue(Schema::hasTable(config('meta-field.table')));
    }


    /**
     * @test
     */
    public function the_meta_field_table_can_be_rolled_back() {

        include_once(__DIR__ . '/../database/migrations/create_meta_fields_table.php.stub');

        (new \CreateMetaFieldsTable)->up();

        $this->assertNull((new \CreateMetaFieldsTable)->down());

        $this->assertFalse(Schema::hasTable(config('meta-field.table')));
    }


    /**
     * @test
     */
    public function the_migration_will_throw_exception_if_defined_config_table_already_exists() {

        include_once(__DIR__ . '/../database/migrations/create_meta_fields_table.php.stub');

        (new \CreateMetaFieldsTable)->up();

        $this->expectException(Exception::class);

        (new \CreateMetaFieldsTable)->up();
    }

}