<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /** --------- GitHub --------- */
    public function redirect()
    {
        return Socialite::driver('github')
            ->scopes(['read:user', 'user:email'])
            ->redirect();
    }

    public function callback()
    {
        $git = Socialite::driver('github')->stateless()->user();

        $email = $git->getEmail();
        if (!$email && $git->token) {
            $resp = Http::withHeaders([
                'Authorization' => "token {$git->token}",
                'Accept' => 'application/vnd.github+json',
            ])->get('https://api.github.com/user/emails')->json();

            if (is_array($resp)) {
                $primary = collect($resp)->firstWhere('primary', true);
                $email = $primary['email'] ?? (collect($resp)->firstWhere('verified', true)['email'] ?? null);
            }
        }

        if (!$email) {
            return redirect('/login')->withErrors([
                'email' => 'No email from GitHub. Make your email visible or try Google login.',
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $git->getName() ?: ($git->getNickname() ?: 'User ' . Str::random(5)),
                'password' => bcrypt(Str::random(32)),
            ]
        );

        Auth::login($user, true);
        return redirect('/dashboard');
    }

    /** --------- Google --------- */
    public function googleRedirect()
    {
        // default scopes are fine (openid, email, profile)
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback()
    {
        $google = Socialite::driver('google')->stateless()->user();

        $email = $google->getEmail();
        if (!$email) {
            return redirect('/login')->withErrors([
                'email' => 'No email received from Google.',
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $google->getName() ?: ($google->getNickname() ?: 'User ' . Str::random(5)),
                'password' => bcrypt(Str::random(32)),
            ]
        );

        Auth::login($user, true);
        return redirect('/dashboard');
    }
}
