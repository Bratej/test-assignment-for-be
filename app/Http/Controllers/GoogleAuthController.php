<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            if (User::where('email', $googleUser->getEmail())->whereNull('google_id')->first()) {
                return redirect()->route('login')->withErrors([
                    'email' => 'The email address already exists. Please log in or use a different account.'
                ]);
            }
            $user = User::firstOrCreate(
                [
                    'google_id' => $googleUser->getId(),
                ],
                [
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                ]
            );

            Auth::login($user);
            return redirect()->route('home');
        } catch (\Exception $e) {
            Log::error('Error while logging with Google SSO: ' . $e->getMessage());
            return redirect()->route('login')->withErrors();
        }
    }
}
