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
<div class="justify-center px-24 py-16 mt-3 border border-black rounded-3xl">
    <div class="px-10 text-sm text-center text-black md:text-xl md:text-bold w-100">
        {{ __('Check your email for the OTP pin, If it doesn’t reflect in your inbox,') }}<br>{{ __('consider checking Spam mail') }}
    </div>

    <!-- ====== OTP Start -->
    <form wire:submit='verify' id="otp-form" class="flex w-100 ">
        <div class="justify-center w-full py-16 bg-white px-28">
            <div class="flex justify-evenly">
                <input wire:model='No1' name="No1" type="text" maxlength="1"
                    class="shadow-xs flex w-[100px] h-[100px] items-center justify-center rounded-lg border border-stroke bg-white p-1 text-center text-2xl font-medium text-gray-5 outline-none sm:text-4xl" />
                <input wire:model='No2' name="No2" type="text" maxlength="1"
                    class="shadow-xs flex w-[100px] h-[100px] items-center justify-center rounded-lg border border-stroke bg-white p-2 text-center text-2xl font-medium text-gray-5 outline-none sm:text-4xl dark:border-dark-3 dark:bg-white/5" />
                <input wire:model='No3' name="No3" type="text" maxlength="1"
                    class="shadow-xs flex w-[100px] h-[100px] items-center justify-center rounded-lg border border-stroke bg-white p-2 text-center text-2xl font-medium text-gray-5 outline-none sm:text-4xl dark:border-dark-3 dark:bg-white/5" />
                <input wire:model='No4' name="No4" type="text" maxlength="1"
                    class="shadow-xs flex w-[100px] h-[100px] items-center justify-center rounded-lg border border-stroke bg-white p-2 text-center text-2xl font-medium text-gray-5 outline-none sm:text-4xl dark:border-dark-3 dark:bg-white/5" />
                <input x-on:input="document.getElementById('verify').click()" wire:model='No5' name="No5"
                    type="text" maxlength="1"
                    class="shadow-xs flex w-[100px] h-[100px] items-center justify-center rounded-lg border border-stroke bg-white p-2 text-center text-2xl font-medium text-gray-5 outline-none sm:text-4xl dark:border-dark-3 dark:bg-white/5" />

            </div>
        </div>
        <!-- ====== OTP End -->


        @if ($errors->all())
            <p>Incomplete Pin</p>
        @endif



        <x-primary-button hidden id="verify">
            {{ __('Verify') }}
        </x-primary-button>


        @if (session('status') == 'verification-link-sent')
            <div class=" ml-[10%]  mt-[13.5%]  px-28 font-medium text-sm text-green-600   text-center z-50 absolute">
                Check
                your
                email
                for the OTP
                pin, If
                it
                doesn’t reflect in your inbox, consider checking Spam mail. <br>
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif
        @if (session('status') == 'verification-successful')
            <div
                class=" ml-[28%]  mt-[15%] px-20 font-bold text-green-600 dark:text-green-400  text-center z-50 absolute">
                {{ __('Verification Successful') }}
            </div>
        @endif
        @if (session('status') == 'Invalid OTP')
            <div class="ml-[31%]  mt-[15%] px-20 font-bold text-md text-red-600 z-50 absolute ">
                {{ __('Invalid OTP') }}
            </div>
        @endif


    </form>
    <div class="justify-center mt-1 text-center w-100">
        <div class="h-4 text-2xl "> Time Left: <span id="countdown">{{ config('otp.expiry') }}:00</span>
        </div>
        <x-primary-button @click="startTimer()" wire:click="sendVerification">
            {{ __('Resend Verification Email') }}
        </x-primary-button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("otp-form");
            const inputs = [...form.querySelectorAll("input[type=text]")];
            const submit = form.querySelector('button[type=submit]')

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
                    } else {
                        // submit.focus()
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
                // submit.focus()
            };

            inputs.forEach((input) => {
                input.addEventListener("input", handleInput);
                input.addEventListener("keydown", handleKeyDown);
                input.addEventListener("focus", handleFocus);
                input.addEventListener("paste", handlePaste);
            });
        });

        let countdownDate = new Date().getTime() + ({{ config('otp.expiry') }} * 60 * 1000); // 5 minutes from now


        let startTimer = () => {
            let x = setInterval(function() {
                let now = new Date().getTime();
                let distance = countdownDate - now;

                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById("countdown").innerHTML =
                    ("0" + minutes).slice(-2) + ":" + ("0" + seconds).slice(-2);

                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("countdown").innerHTML = "EXPIRED";
                }
            }, 1000);
        }

        document.addEventListener("DOMContentLoaded", startTimer());
    </script>
</div>
