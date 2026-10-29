<?php

namespace Touhidurabir\MetaFields\Tests;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Route;
use Touhidurabir\MetaFields\Tests\App\FormRequests\StoreUser;
use Touhidurabir\MetaFields\Tests\Traits\LaravelTestBootstrapping;

class PrecognitionTest extends TestCase
{
    use LaravelTestBootstrapping;

    /**
     * Precognition was added in late Laravel 9 releases
     *
     * @var string
     */
    protected const PRECOGNITION_MIDDLEWARE = 'Illuminate\\Foundation\\Http\\Middleware\\HandlePrecognitiveRequests';


    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {

        parent::setUp();

        if (! class_exists(static::PRECOGNITION_MIDDLEWARE)) {

            $this->markTestSkipped('Precognition is not available in this Laravel version.');
        }
    }


    /**
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {

        if (! class_exists(static::PRECOGNITION_MIDDLEWARE)) {

            return;
        }

        Route::post('/users', function (StoreUser $request) {

            return response()->json($request->metas());

        })->middleware([static::PRECOGNITION_MIDDLEWARE]);
    }


    /**
     * @test
     */
    #[Test]
    public function a_precognitive_request_only_validates_the_requested_fields()
    {

        $this
            ->postJson('/users', ['email' => 'testmail1@test.com'], [
                'Precognition' => 'true',
                'Precognition-Validate-Only' => 'email',
            ])
            ->assertStatus(204);
    }


    /**
     * @test
     */
    #[Test]
    public function a_precognitive_request_validates_requested_meta_fields()
    {

        $this
            ->postJson('/users', ['email' => 'testmail1@test.com', 'bio' => ['not a string']], [
                'Precognition' => 'true',
                'Precognition-Validate-Only' => 'email,bio',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['bio'])
            ->assertJsonMissingValidationErrors(['password']);
    }


    /**
     * @test
     */
    #[Test]
    public function a_non_precognitive_request_still_validates_all_fields()
    {

        $this
            ->postJson('/users', ['email' => 'testmail1@test.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password', 'bio']);
    }
}
