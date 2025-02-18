<?php

use App\Enums\OtpType;
use App\Models\Otp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public array $otp = ['', '', '', '', '', ''];
    public string $errorMessage = '';
    public string $successMessage = '';

    #[On('otp-paste')]
    public function pasteOtp(string $code)
    {
        if (strlen($code) === 6 && is_numeric($code)) {
            $this->otp = str_split($code);
            $this->verifyOtp();
        } else {
            $this->errorMessage = 'Invalid OTP format.';
        }
    }

    public function updatedOtp()
    {
        if (implode('', $this->otp) !== '' && count(array_filter($this->otp)) === 6) {
            $this->verifyOtp();
        }
    }

    public function verifyOtp()
    {
        try {
            $user = Auth::user();
            $otp = Otp::verifyOtp($user, implode('', $this->otp), \App\Enums\OtpType::EMAIL);
            $this->errorMessage = ''; // Clear errors if successful
            $otp->delete();
            Session::put('otp_verified', true);
            return redirect()->route('home'); 
        } catch (\App\Exceptions\OtpException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function generateOtp()
    {
        $user = Auth::user();
        $key = 'otp_request_' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->errorMessage = 'Too many OTP requests. Try again later.';
            return;
        }

        RateLimiter::hit($key, now()->addMinutes(10));

        // Generate and store OTP
        Otp::generateOtp($user, OtpType::EMAIL);

        $this->successMessage = 'A new OTP has been sent to your email.';
        $this->errorMessage = '';
    }
}
?>

<div class="container d-flex flex-column align-items-center mt-4">
    <h1>OTP Verification</h1>
    <div class="d-flex gap-2">
        @foreach ($otp as $index => $digit)
        <input
            type="text"
            class="form-control text-center fs-4 border border-primary"
            maxlength="1"
            x-data="{ focusNext(el) { setTimeout(() => el?.focus(), 10) } }"
            x-on:input="focusNext($el.nextElementSibling)"
            wire:model.lazy="otp.{{ $index }}" />
        @endforeach
    </div>

    @if ($errorMessage)
    <div class="alert alert-danger mt-3" role="alert">{{ $errorMessage }}</div>
    @endif
    @if ($successMessage)
    <div class="alert alert-success mt-3" role="alert">{{ $successMessage }}</div>
    @endif
    <button wire:click="generateOtp"
        class="btn btn-primary mt-3">
        Generate OTP
    </button>
</div>

<script>
    document.addEventListener('paste', (event) => {
        let code = (event.clipboardData || window.clipboardData).getData('text');
        window.Livewire.dispatch('otp-paste', {
            code
        });
    });
</script>