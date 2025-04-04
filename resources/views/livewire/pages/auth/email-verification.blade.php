<?php

use App\Models\User;
use Livewire\Volt\Volt;
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
    public bool $status = false;
    public int $timeLeft = 300; // 5 minutes in seconds
    public bool $resent = false;
    public bool $disabled = false;
    private $timer;

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

            switch ($user->role) {
                case 'admin':
                    $this->redirect(route('admin.dashboard', absolute: false));
                    break;

                case 'creative':
                    session()->reflash();
                    session()->put('user', $user);
                    $this->redirect(route('creative.payment.preference', absolute: false));
                    break;

                case 'user':
                    $this->redirect(route('dashboard', absolute: false));
                    break;
            }
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
<div class="px-6 py-16 mx-auto mt-20 border border-black md:mt-10 rounded-3xl">
    <div class="px-6 text-sm font-semibold text-center text-black md:text-lg lg:text-xl">
        <p>{{ __('Check your email for the OTP pin, If it doesn’t reflect in your inbox') }}</p>
        <span> {{ __('consider checking Spam mail') }}</span>
    </div>

    <form wire:submit='verify' id="otp-form" class="w-full">
        <div class="py-8 bg-white">
            <div class="flex justify-center space-x-4">
                <input wire:model='No1' name="No1" type="text" maxlength="1"
                    class="w-10 h-10 p-1 text-2xl font-medium text-center bg-white border rounded-lg shadow-xs outline-none md:w-20 md:h-20 border-stroke text-gray-5 sm:text-4xl" />
                <input wire:model='No2' name="No2" type="text" maxlength="1"
                    class="w-10 h-10 p-2 text-2xl font-medium text-center bg-white border rounded-lg shadow-xs outline-none md:w-20 md:h-20 border-stroke text-gray-5 sm:text-4xl dark:border-dark-3 dark:bg-white/5" />
                <input wire:model='No3' name="No3" type="text" maxlength="1"
                    class="w-10 h-10 p-2 text-2xl font-medium text-center bg-white border rounded-lg shadow-xs outline-none md:w-20 md:h-20 border-stroke text-gray-5 sm:text-4xl dark:border-dark-3 dark:bg-white/5" />
                <input wire:model='No4' name="No4" type="text" maxlength="1"
                    class="w-10 h-10 p-2 text-2xl font-medium text-center bg-white border rounded-lg shadow-xs outline-none md:w-20 md:h-20 border-stroke text-gray-5 sm:text-4xl dark:border-dark-3 dark:bg-white/5" />
                <input x-on:input="document.getElementById('verify').click()" wire:model='No5' name="No5"
                    type="text" maxlength="1"
                    class="w-10 h-10 p-2 text-2xl font-medium text-center bg-white border rounded-lg shadow-xs outline-none md:w-20 md:h-20 border-stroke text-gray-5 sm:text-4xl dark:border-dark-3 dark:bg-white/5" />
            </div>
        </div>
        @if ($errors->all())
            <p class="mt-2 text-center text-red-500" x-cloak="display:hidden">{{ __('Incomplete Pin') }}</p>
        @endif

        <x-primary-button hidden id="verify">
            {{ __('Verify') }}
        </x-primary-button>

        @if (session('status') == 'verification-link-sent')
            <div class="absolute left-0 right-0 px-6 mt-4 text-sm font-medium text-center text-green-600"
                x-cloak="display:hidden">
                {{ __('Check your email for the OTP pin, If it doesn’t reflect in your inbox, consider checking Spam mail.') }}
                <br>
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        @if (session('status') == 'verification-successful')
            <div class="absolute left-0 right-0 mt-4 font-bold text-center text-green-600" x-cloak="display:hidden">
                {{ __('Verification Successful') }}
            </div>
        @endif

        @if (session('status') == 'Invalid OTP')
            <div class="absolute left-0 right-0 font-bold text-center text-red-600 md:mt-4 text-md"
                x-cloak="display:hidden">
                {{ $disabled = false }}

                {{ __('Invalid OTP') }}
            </div>
        @endif
    </form>


    <div class="mt-6 text-center">
        <div class="text-xl font-semibold" id="timer">
            {{ __('Time Left:') }} <span id="countdown">{{ config('otp.expiry') }}:00</span>
        </div>
        <button
            class=" mt-14'inline-flex items-center px-3 py-3 bg-navy-blue border border-transparent rounded-md font-semibold text-xl text-white  capitalize tracking-widest hover:bg-white hover:text-navy-blue hover:border-navy-blue border-navy-blue  focus:bg-white  active:bg-navy-blue active:text-white  focus:outline-none focus:ring-2 focus:ring-navy-blue focus:ring-offset-2  transition ease-in-out duration-150"
            @click="startTimer(),$wire.sendVerification()">
            {{ __('Resend Verification Email') }}
        </button>


    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("otp-form");
            const inputs = [...form.querySelectorAll("input[type=text]")];
            const submit = form.querySelector('button[type=submit]');


            const handleKeyDown = (e) => {
                if (
                    !/^[0-9]{1}$/.test(e.key) &&
                    e.key !== "Backspace" &&
                    e.key !== "Delete" &&
                    e.key !== "Tab" &&
                    !e.metaKey
                ) {
                    e.preventDefault();
                }

                if (e.key === "Delete" || e.key === "Backspace") {
                    const index = inputs.indexOf(e.target);
                    if (index > 0) {
                        inputs[index - 1].value = "";
                        inputs[index - 1].focus();
                    }
                }
            };

            const handleInput = (e) => {
                const {
                    target
                } = e;
                const index = inputs.indexOf(target);
                if (target.value) {
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                }
            };

            const handleFocus = (e) => {
                e.target.select();
            };

            const handlePaste = (e) => {
                e.preventDefault();
                const text = e.clipboardData.getData("text");
                if (!new RegExp(`^[0-9]{${inputs.length}}$`).test(text)) {
                    return;
                }
                const digits = text.split("");
                inputs.forEach((input, index) => (input.value = digits[index]));
                // submit.focus() // Consider if you still need this
            };

            inputs.forEach((input) => {
                input.addEventListener("input", handleInput);
                input.addEventListener("keydown", handleKeyDown);
                input.addEventListener("focus", handleFocus);
                input.addEventListener("paste", handlePaste);
            });
        });

        let countdownDate = new Date().getTime() + ({{ config('otp.expiry') }} * 60 * 1000); // 5 minutes from now
        let countdownInterval;

        const updateCountdown = () => {
            const now = new Date().getTime();
            const distance = countdownDate - now;

            const minutes = Math.max(0, Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)));
            const seconds = Math.max(0, Math.floor((distance % (1000 * 60)) / 1000));

            document.getElementById("countdown").innerHTML =
                (`${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`);

            if (distance < 0) {
                clearInterval(countdownInterval);
                document.getElementById("countdown").innerHTML = "00:00";

            }
        };

        const startTimer = () => {
            updateCountdown(); // Initial call to avoid a brief flash of the initial time
            countdownInterval = setInterval(updateCountdown, 1000);
        };

        document.addEventListener("DOMContentLoaded", startTimer);
    </script>
</div>
