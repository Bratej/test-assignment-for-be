<?php

namespace Tests\Feature;

use Illuminate\Auth\Notifications\ResetPassword;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use DatabaseMigrations;
    use RefreshDatabase;

    protected $plainTextPassword;

    protected $user;

    protected $loginRoute;

    protected $homeRoute;

    public function setUp(): void
    {
        parent::setUp();
        $this->plainTextPassword = fake()->password(8, 10);
        $this->user = User::factory()->create([
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => bcrypt($this->plainTextPassword),
        ]);
        $this->loginRoute = route('login');
        $this->homeRoute = route('home');
    }

    public function testUserCanViewTheLoginForm()
    {
        $response = $this->get($this->loginRoute);
        $response->assertSuccessful();
        $response->assertViewIs('auth.login');
    }

    public function testUserCanLogin()
    {
        $response = $this->post($this->loginRoute, [
            'email' => $this->user->email,
            'password' => $this->plainTextPassword,
        ]);

        $response->assertRedirect($this->homeRoute);
        $this->assertAuthenticatedAs($this->user);
    }

    public function testUserCannotViewTheLoginFormWhenAuthenticated()
    {
        $response = $this->actingAs($this->user)->get($this->loginRoute);

        $response->assertRedirect($this->homeRoute);
    }

    public function testUserCannotLoginWithIncorrectPassword()
    {
        $response = $this->from($this->loginRoute)->post($this->loginRoute, [
            'email' => $this->user->email,
            'password' => fake()->password(),
        ]);
        $response->assertRedirect($this->loginRoute);
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function testUserLoginWithRememberMe()
    {
        $response = $this->post($this->loginRoute, [
            'email' => $this->user->email,
            'password' => $this->plainTextPassword,
            'remember' => 1,
        ]);

        // Cookie
        $cookie = implode('|', [$this->user->id, $this->user->getRememberToken(), $this->user->password]);

        $response->assertRedirect($this->homeRoute);
        $response->assertCookie(Auth::guard()->getRecallerName(), $cookie);
        $this->assertAuthenticatedAs($this->user);
    }

    public function testForgottenPasswordTokenAndEmail()
    {
        Notification::fake();
        $this->post(route('password.email'), [
            'email' => $this->user->email
        ]);
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $this->user->email)
            ->first();

        $this->assertNotNull($resetToken->token);
        Notification::assertSentTo($this->user, ResetPassword::class, function ($notification, $channels) use ($resetToken) {
            return Hash::check($notification->token, $resetToken->token) === true;
        });
    }
}
