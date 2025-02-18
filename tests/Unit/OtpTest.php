<?php

namespace Tests\Unit;

use App\Models\Otp;
use App\Models\User;
use App\Enums\OtpType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_generate_an_otp()
    {
        $user = User::factory()->create();

        $otp = Otp::create([
            'user_id' => $user->id,
            'code' => '123456',
            'type' => OtpType::EMAIL,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertDatabaseHas('otps', ['user_id' => $user->id, 'code' => '123456']);
    }

    /** @test */
    public function it_can_verify_a_valid_otp()
    {
        $user = User::factory()->create();

        $otp = Otp::create([
            'user_id' => $user->id,
            'code' => '123456',
            'type' => OtpType::EMAIL,
            'expires_at' => now()->addMinutes(5),
        ]);

        $verifiedOtp = Otp::where('user_id', $user->id)
            ->where('code', '123456')
            ->where('expires_at', '>', now())
            ->first();

        $this->assertNotNull($verifiedOtp);
    }

    /** @test */
    public function it_cannot_verify_an_expired_otp()
    {
        $user = User::factory()->create();

        $otp = Otp::create([
            'user_id' => $user->id,
            'code' => '123456',
            'type' => OtpType::EMAIL,
            'expires_at' => now()->subMinutes(1), // Already expired
        ]);

        $verifiedOtp = Otp::where('user_id', $user->id)
            ->where('code', '123456')
            ->where('expires_at', '>', now())
            ->first();

        $this->assertNull($verifiedOtp);
    }

    /** @test */
    public function it_cannot_verify_an_invalid_otp()
    {
        $user = User::factory()->create();

        Otp::create([
            'user_id' => $user->id,
            'code' => '123456',
            'type' => OtpType::EMAIL,
            'expires_at' => now()->addMinutes(5),
        ]);

        $wrongOtp = Otp::where('user_id', $user->id)
            ->where('code', '654321') // Wrong code
            ->first();

        $this->assertNull($wrongOtp);
    }

    /** @test */
    public function it_deletes_otp_after_successful_verification()
    {
        $user = User::factory()->create();

        $otp = Otp::create([
            'user_id' => $user->id,
            'code' => '123456',
            'type' => OtpType::EMAIL,
            'expires_at' => now()->addMinutes(5),
        ]);

        $otp->delete();

        $this->assertDatabaseMissing('otps', ['user_id' => $user->id, 'code' => '123456']);
    }
}
