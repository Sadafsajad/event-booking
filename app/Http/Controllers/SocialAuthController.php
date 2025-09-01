<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        $social = Socialite::driver($provider)->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $social->getEmail()],
            [
                'name' => $social->getName() ?: $social->getNickname() ?: 'User ' . Str::random(5),
                'password' => bcrypt(Str::random(16))
            ]
        );

        Auth::login($user, true);
        return redirect('/dashboard');
    }
}
