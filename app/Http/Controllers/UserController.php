<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Console\Application;
use Illuminate\Http\Request;
use URL;

class UserController extends Controller
{
    public function referral(?String $slug)
    {
        $user = User::where('referral_link', "=", $slug)->first();
        return view('livewire.pages.referral-page', [
            'user' => $user,
            'referral_link' => $slug,
        ]);
    }

    public function userProfile(?String $slug)
    {
        $user = User::where('referral_link', "=", $slug)->first();
        return view('livewire.pages.referral-page', [
            'user' => $user,
            'referral_link' => $slug,
        ]);
    }
}
