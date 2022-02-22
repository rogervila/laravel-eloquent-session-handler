<?php

namespace Tests\EloquentSessionHandler;

use Carbon\Carbon;
use EloquentSessionHandler\ServiceProvider;
use EloquentSessionHandler\Session as SessionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session as LaravelSession;
use Tests\EloquentSessionHandler\Fixtures\User;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class EloquentSessionHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected function deleteMigrations(?Filesystem $filesystem = null)
    {
        $filesystem = is_null($filesystem) ? new Filesystem() : $filesystem;

        if ($filesystem->exists($vendorDir = __DIR__ . '/../vendor')) {
            $filesystem->remove(glob($vendorDir . '/orchestra/testbench-core/laravel/database/migrations/*.php'));
        }
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteMigrations();
        $this->artisan('session:table');

        if (!Schema::hasTable('sessions')) {
            $this->artisan('migrate')->run();
        }
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }


    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('session.driver', 'eloquent');
    }


    public function test_session_interaction(): void
    {
        $this->assertEquals('eloquent', LaravelSession::getDefaultDriver());
        $this->assertEquals(0, SessionModel::count());

        $user = User::create([
            'name' => 'foo',
            'email' => 'foo@bar.com',
            'password' => Hash::make('secret'),
        ]);

        // https://github.com/laravel/framework/blob/8.x/src/Illuminate/Auth/SessionGuard.php#L474
        Auth::guard('web')->login($user);
        $this->assertEquals(Auth::id(), $user->id);

        // Persist session
        LaravelSession::save();

        $this->assertEquals(1, SessionModel::count());

        $session = SessionModel::first();
        $this->assertInstanceOf(Carbon::class, $session->last_activity);
        $this->assertIsArray($session->unserialized_payload);
    }
}
