<?php

use App\Models\User;
use Tzsk\Otp\Facades\Otp;
use Livewire\Volt\Component;
use App\Mail\EmailVerification;
use Livewire\Attributes\Layout;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Auth\Authenticatable;

new #[Layout('layouts.guest')] class extends Component {
    public $user = '';
    public $secret = '';
    public string $No1 = '';
    public string $No2 = '';
    public string $No3 = '';
    public string $No4 = '';
    public string $No5 = '';
    public string $otp = '';

    public function mount()
    {
        $this->user = session('user');
        $this->secret = strval(session('secret'));
    }

    public function verify()
    {
        $pin = $this->validate([
            'No1' => ['required', 'string', 'max:1'],
            'No2' => ['required', 'string', 'max:1'],
            'No3' => ['required', 'string', 'max:1'],
            'No4' => ['required', 'string', 'max:1'],
            'No5' => ['required', 'string', 'max:1'],
        ]);

        $this->otp = $pin['No1'] . $pin['No2'] . $pin['No3'] . $pin['No4'] . $pin['No5'];

        $userOtp = Otp::check($this->otp, $this->secret);

        if ($userOtp) {
            $user = User::where('email', '=', $this->user->email)->first();

            if (!$user) {
                abort(500);
            }

            $user->email_verified_at = now();

            $user->save();

            Auth::login($this->user);

            Session::regenerate(auth()->id());

            Session::flash('status', 'verification-successful');

            if ($user->role == 'admin') {
                return redirect(route('admin.dashboard', absolute: false));
            }

            return redirect(route('dashboard', absolute: false));
        } else {
            Session::flash('status', 'Invalid OTP');
        }
    }

    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        $otp = Otp::generate($this->user->email);

        Mail::to($this->user->email)->send(new EmailVerification($otp, $this->user->name));

        Session::flash('status', 'verification-link-sent');
    }
};
?>
<div class="px-4 py-8 mx-4 mt-12 border border-black rounded-3xl md:mt-10 md:px-10 md:py-16 md:mx-16">
    <div wire:loading
        class="py-3 mb-6 text-white transition-opacity duration-500 border rounded alert-info alert top-5 right-1 bg-navy-blue border-navy-blue absolute"
        role="alert">
        <svg class="inline-block w-6 h-6 text-white animate-spin bw-spinner" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        Loading . . .
    </div>

    <div class="text-center text-black text-sm md:text-lg lg:text-xl font-semibold">
        <p>{{ __('Check your email for the OTP pin, If it doesn’t reflect in your inbox') }}</p>
        <span>{{ __('consider checking Spam mail') }}</span>
    </div>

    <form wire:submit='verify' id="otp-form" class="w-full">


        <div class="py-8 bg-white">
            <div class="flex justify-center gap-2 sm:gap-4">

                <input wire:model='No1' name="No1" inputmode="numeric" pattern="[0-9]*" type="text"
                    maxlength="1"
                    class="w-10 h-10 text-2xl sm:w-14 sm:h-14 md:w-20 md:h-20 p-2 font-medium text-center bg-white border rounded-lg shadow outline-none border-stroke text-gray-5 sm:text-4xl" />
                <input wire:model='No2' name="No2" inputmode="numeric" pattern="[0-9]*" type="text"
                    maxlength="1"
                    class="w-10 h-10 text-2xl sm:w-14 sm:h-14 md:w-20 md:h-20 p-2 font-medium text-center bg-white border rounded-lg shadow outline-none border-stroke text-gray-5 sm:text-4xl" />
                <input wire:model='No3' name="No3" inputmode="numeric" pattern="[0-9]*" type="text"
                    maxlength="1"
                    class="w-10 h-10 text-2xl sm:w-14 sm:h-14 md:w-20 md:h-20 p-2 font-medium text-center bg-white border rounded-lg shadow outline-none border-stroke text-gray-5 sm:text-4xl" />
                <input wire:model='No4' name="No4" inputmode="numeric" pattern="[0-9]*" type="text"
                    maxlength="1"
                    class="w-10 h-10 text-2xl sm:w-14 sm:h-14 md:w-20 md:h-20 p-2 font-medium text-center bg-white border rounded-lg shadow outline-none border-stroke text-gray-5 sm:text-4xl" />
                <input x-on:input="$wire.verify();" inputmode="numeric" pattern="[0-9]*" wire:model='No5' name="No5"
                    type="text" maxlength="1"
                    class="w-10 h-10 text-2xl sm:w-14 sm:h-14 md:w-20 md:h-20 p-2 font-medium text-center bg-white border rounded-lg shadow outline-none border-stroke text-gray-5 sm:text-4xl" />

            </div>
        </div>

        <div class="flex items-center justify-center mt-4">

            @if ($errors->all())
                <p class="mt-2 text-center text-red-500 absolute font-bold">{{ __('Incomplete Pin') }}</p>
            @endif

            @if (session('status') === 'verification-link-sent')
                <div class="mt-2 text-sm font-medium text-center text-green-600 absolute ">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            @if (session('status') === 'verification-successful')
                <div class="mt-4 font-bold text-center text-green-600 absolute">
                    {{ __('Verification Successful') }}
                </div>
            @endif

            @if (session('status') === 'Invalid OTP')
                <div class="mt-4 font-bold text-center text-red-600 absolute">
                    {{ __('Invalid OTP') }}
                </div>
            @endif
        </div>
    </form>

    <div class="mt-12 text-center">
        <div class="text-xl font-semibold" id="timer">
            {{ __('Time Left:') }} <span id="countdown">{{ config('otp.expiry') }}:00</span>
        </div>
        <button
            class="mt-5 px-4 py-3 text-xl font-semibold text-white transition duration-150 ease-in-out bg-navy-blue border border-transparent rounded-md hover:bg-white hover:text-navy-blue hover:border-navy-blue focus:outline-none focus:ring-2 focus:ring-navy-blue focus:ring-offset-2"
            @click="startTimer(); $wire.sendVerification()">
            {{ __('Resend Verification Email') }}
        </button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("otp-form");
            const inputs = Array.from(form.querySelectorAll("input[type=text]"));

            inputs.forEach((input, idx) => {
                input.addEventListener("input", (e) => {
                    const val = e.target.value;
                    if (val.length > 1) e.target.value = val.slice(0, 1);
                    if (val && idx < inputs.length - 1) inputs[idx + 1].focus();
                });

                input.addEventListener("keydown", (e) => {
                    if (e.key === "Backspace" && !input.value && idx > 0) {
                        inputs[idx - 1].focus();
                    }
                });
            });

            const firstEmpty = inputs.find(input => !input.value) || inputs[0];
            firstEmpty.focus();
        });

        let countdownInterval;
        const updateCountdown = () => {
            const countdownElement = document.getElementById("countdown");
            let time = countdownElement.textContent.split(":");
            let minutes = parseInt(time[0]);
            let seconds = parseInt(time[1]);

            if (minutes === 0 && seconds === 0) {
                clearInterval(countdownInterval);
                countdownElement.innerText = "00:00";
                return;
            }

            if (seconds === 0) {
                minutes--;
                seconds = 59;
            } else {
                seconds--;
            }

            countdownElement.innerText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        };

        const startTimer = () => {
            clearInterval(countdownInterval);
            document.getElementById("countdown").innerText = "{{ config('otp.expiry') }}:00";
            countdownInterval = setInterval(updateCountdown, 1000);
        };

        document.addEventListener("DOMContentLoaded", startTimer);
    </script>
</div>
