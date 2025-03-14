<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function googleLogin()
    {
        return Socialite::driver(driver: 'google')->redirect();
    }

    public function googleSignup()
    {
        return Socialite::driver(driver: 'google')->redirect();
    }

    public function googleAuthentication(Request $request)
    {
        $googleUser = Socialite::driver('google')->user();

        // User Credentials
        $firstname =  $googleUser->user['given_name'];
        $lastname =  $googleUser->user['family_name'];
        $avatar = $googleUser->avatar;
        $email = $googleUser->email;
        $google_id = $googleUser->id;

        $user = User::where('google_id', $google_id)->first();

        try {
            if ($user) {
                // Login Authenticated User
                Auth::login($user);
                return redirect(route('dashboard', absolute: false));
            } else {

                // Redirection To Registration Completion Page
                return  redirect(route('complete.registration'))->with([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'avatar' => $avatar,
                    'email' => $email,
                    'google_id' => $google_id,

                ]);
            }
        } catch (\Exception $error) {
            return  $error;
        }
    }
}
