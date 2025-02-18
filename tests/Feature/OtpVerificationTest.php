<?php

namespace Tests\Feature;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_renders_the_otp_component()
    {
        $this->actingAs(User::factory()->create());

        Volt::test('otp-input')
            ->assertSee('OTP Verification');
    }

    /** @test */
    public function it_updates_otp_input()
    {
        $this->actingAs(User::factory()->create());

        $component = Volt::test('otp-input');
        $component->set('otp', ['1', '2', '3', '4', '5', '6']);
        $component->assertSet('otp', ['1', '2', '3', '4', '5', '6']);
    }

    /** @test */
    public function it_verifies_valid_otp()
    {
        $user = User::factory()->create();

        $otp = Otp::create([
            'user_id' => $user->id,
            'code' => '123456',
            'type' => 'email',
            'expires_at' => now()->addMinutes(5),
        ]);

        $component = Volt::test('otp-input');
        $component->actingAs($user);
        $component->set('otp', ['1', '2', '3', '4', '5', '6']);
        $component->call('verifyOtp');

        // Ensure OTP is deleted
        $this->assertTrue(session()->has('otp_verified'));
        $this->assertDatabaseMissing('otps', ['user_id' => $user->id, 'code' => '123456']);
    }

    /** @test */
    public function it_fails_with_invalid_otp()
    {
        $user = User::factory()->create();
        Otp::create([
            'user_id' => $user->id,
            'code' => '123456',
            'type' => 'email',
            'expires_at' => now()->addMinutes(5),
        ]);

        $component = Volt::test('otp-input');
        $component->actingAs($user);
        $component->set('otp', ['6', '5', '4', '3', '2', '1']); // Incorrect OTP
        $component->call('verifyOtp')
            ->assertSee('Invalid, expired, or already used OTP.')
            ->assertNotDispatched('otp-success');

        // Ensure OTP still exists
        $this->assertDatabaseHas('otps', ['user_id' => $user->id, 'code' => '123456']);
    }

    /** @test */
    public function it_rejects_expired_otp()
    {
        $user = User::factory()->create();
        Otp::create([
            'user_id' => $user->id,
            'code' => '123456',
            'type' => 'email',
            'expires_at' => now()->subMinutes(1), // Expired
        ]);

        $component = Volt::test('otp-input');
        $component->actingAs($user);
        $component->set('otp', ['1', '2', '3', '4', '5', '6']);
        $component->call('verifyOtp')
            ->assertSee('Invalid, expired, or already used OTP.')
            ->assertNotDispatched('otp-success');
    }
}
