<?php

namespace Tests\EloquentSessionHandler;

use Carbon\Carbon;
use EloquentSessionHandler\ServiceProvider;
use EloquentSessionHandler\Session as SessionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session as LaravelSession;
use Tests\EloquentSessionHandler\Fixtures\User;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class EloquentSessionHandlerTest extends TestCase
{
    use RefreshDatabase;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    protected $filesystem;

    protected function deleteMigrations(): void
    {
        if ($this->filesystem->exists($vendorDir = __DIR__ . '/../vendor')) {
            /** @phpstan-ignore-next-line */
            $this->filesystem->remove(glob($vendorDir . '/orchestra/testbench-core/laravel/database/migrations/*.php'));
        }
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();

        parent::setUp();

        $this->deleteMigrations();

        $this->artisan('session:table');

        if (!Schema::hasTable('sessions')) {
            /** @phpstan-ignore-next-line */
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
        $app['config']->set('session.models.user', User::class);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'url' => null,
            'prefix'   => '',
            'foreign_key_constraints' => true,
        ]);
    }


    public function test_session_interaction(): void
    {
        $this->assertEquals('eloquent', LaravelSession::getDefaultDriver());
        $this->assertEquals(0, SessionModel::count());

        $user = User::fake();

        // https://github.com/laravel/framework/blob/8.x/src/Illuminate/Auth/SessionGuard.php#L474
        Auth::login($user);
        $this->assertEquals(Auth::id(), $user->id);

        // Persist session
        LaravelSession::save();

        $this->assertEquals(1, SessionModel::count());

        $session = SessionModel::firstOrFail();
        $this->assertInstanceOf(Carbon::class, $session->last_activity);
        $this->assertIsArray($session->unserialized_payload);
    }

    public function test_session_user_relationship(): void
    {
        $id = uniqid();

        $user = User::fake(['email' => 'foo@foo.com']);

        SessionModel::insert([
            'id' => $id,
            'user_id' => $user->id,
            'ip_address' => '0.0.0.0',
            'user_agent' => 'Bot',
            'payload' => 'whatever',
            'last_activity' => Carbon::now(),
        ]);

        $session = SessionModel::find($id);

        $this->assertNotNull($session);

        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(User::class, $session->user);
        /** @phpstan-ignore-next-line */
        $this->assertEquals($user->id, $session->user->id);
    }
}
