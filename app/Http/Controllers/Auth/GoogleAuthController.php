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
            // Check if a user exists with the given email
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {

                    return redirect(route('login'))->with('status', 'Kindly login with your email and password');

            }

            // Check if the user exists via Google ID
            $user = User::where('google_id', $google_id)->first();

            if ($user) {
                Auth::login($user);
                return redirect()->route('dashboard');
            }

            // Redirect to registration completion
            return redirect()->route('complete.registration')->with([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'avatar' => $avatar,
                'email' => $email,
                'google_id' => $google_id,
            ]);
        } catch (\Exception $e) {
            session('status', "Google login failed:" . $e->getMessage());
            return redirect()->route('login')->withErrors('Something went wrong. Please try again.');
        }
    }
}
