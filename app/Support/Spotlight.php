<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

class Spotlight
{
    public function search(Request $request)
    {
        // Example of security concern
        // Guests can not search
        if (! Auth::user()) {
            return collect()
                ->merge($this->actions(search: $request->search))
                ->merge($this->users($request->search));
        }

        if (Auth::user()) {
            return collect()
                ->merge($this->users($request->search));
        }
    }

    // Database search
    public function users(string $search = '')
    {
        return User::query()
            ->where('firstname', 'like', "%$search%")
            ->orWhere('lastname', 'like', "%$search%")
            ->orWhere('instagram', 'like', "%$search%")
            ->take(5)
            ->get()
            ->map(function (User $user) {
                return [
                    'avatar' => $user->avatar ? asset('uploads/avatar/' . $user->avatar) : asset('assets/icons-user.png'),
                    'name' => "$user->firstname $user->lastname",
                    'description' => $user->email,
                    'link' => $user->referral_link
                ];
            });
    }

    // Static search, but it could come from a database
    public function actions(string $search = '')
    {
        $icon = Blade::render("<x-mary-icon name='o-user' class='w-11 h-11 p-2 bg-yellow-50 rounded-full' />");

        return collect([
            [
                'name' => 'Register',
                'description' => 'Create A Twellr Account',
                'icon' => $icon,
                'link' => route('register'),
            ],
        ]);
    }
}