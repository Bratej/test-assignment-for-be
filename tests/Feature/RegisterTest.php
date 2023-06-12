<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected $homeRoute;

    protected $registerRoute;

    protected $user;

    protected $newFakeUser;

    protected $plainTextPassword;

    public function setUp(): void
    {
        parent::setUp();

        $this->plainTextPassword = fake()->password(8, 10);
        $this->newFakeUser = [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => bcrypt($this->plainTextPassword),
        ];
        $this->user = User::factory()->create($this->newFakeUser);
        $this->registerRoute = route('register');
        $this->homeRoute = route('home');
    }

    public function testUserCanViewARegistrationForm()
    {
        $response = $this->get($this->registerRoute);

        $response->assertSuccessful();
        $response->assertViewIs('auth.register');
    }

    public function testUserCannotViewTheRegistrationFormWhenAuthenticated()
    {
        $response = $this->actingAs($this->user)->get($this->registerRoute);

        $response->assertRedirect($this->homeRoute);
    }

    public function testUserCanRegister()
    {
        Event::fake();
        $fakeUser = [
            'name' => fake()->firstName() . ' ' . fake()->lastName(),
            'email' => fake()->email(),
            'password' => $this->plainTextPassword,
            'password_confirmation' => $this->plainTextPassword,
        ];
        $response = $this->post($this->registerRoute, $fakeUser);

        $response->assertRedirect($this->homeRoute);
        $this->assertCount(2, $users = User::all());
        $this->assertAuthenticatedAs($user = $users->where('email', $fakeUser['email'])->first());
        $this->assertEquals($fakeUser['name'], $user->name);
        $this->assertEquals($fakeUser['email'], $user->email);
        $this->assertTrue(Hash::check($fakeUser['password'], $user->password));
        Event::assertDispatched(Registered::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }


    public function testUserCannotRegisterWithoutName()
    {
        $response = $this->from($this->registerRoute)->post($this->registerRoute, [
            'name' => '',
            'email' => fake()->email(),
            'password' => $this->plainTextPassword,
            'password_confirmation' => $this->plainTextPassword,
        ]);
        $users = User::all();

        $this->assertCount(1, $users);
        $response->assertRedirect($this->registerRoute);
        $response->assertSessionHasErrors('name');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithoutEmail()
    {
        $response = $this->from($this->registerRoute)->post($this->registerRoute, [
            'name' => fake()->name(),
            'email' => '',
            'password' => $this->plainTextPassword,
            'password_confirmation' => $this->plainTextPassword,
        ]);
        $users = User::all();

        $this->assertCount(1, $users);
        $response->assertRedirect($this->registerRoute);
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithInvalidEmail()
    {
        $response = $this->from($this->registerRoute)->post($this->registerRoute, [
            'name' => fake()->name(),
            'email' => 'test',
            'password' => $this->plainTextPassword,
            'password_confirmation' => $this->plainTextPassword,
        ]);

        $users = User::all();

        $this->assertCount(1, $users);
        $response->assertRedirect($this->registerRoute);
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithoutPassword()
    {
        $response = $this->from($this->registerRoute)->post($this->registerRoute, [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => '',
            'password_confirmation' => $this->plainTextPassword,
        ]);

        $users = User::all();

        $this->assertCount(1, $users);
        $response->assertRedirect($this->registerRoute);
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithoutPasswordConfirmation()
    {
        $response = $this->from($this->registerRoute)->post($this->registerRoute, [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => $this->plainTextPassword,
            'password_confirmation' => '',
        ]);

        $users = User::all();

        $this->assertCount(1, $users);
        $response->assertRedirect($this->registerRoute);
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithPasswordsNotMatching()
    {
        $response = $this->from($this->registerRoute)->post($this->registerRoute, [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => $this->plainTextPassword,
            'password_confirmation' => fake()->password(8, 10),
        ]);

        $users = User::all();

        $this->assertCount(1, $users);
        $response->assertRedirect($this->registerRoute);
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
}
