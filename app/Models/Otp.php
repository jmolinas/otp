<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Enums\OtpType;
use App\Exceptions\OtpException;
use Illuminate\Support\Str;

class Otp extends Model
{
    use HasFactory;

    protected $keyType = 'string'; // UUID as primary key
    public $incrementing = false;

    protected $fillable = ['user_id', 'code', 'expires_at', 'verified_at', 'type'];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'type' => OtpType::class,
    ];

    // Automatically generate UUID when creating the OTP
    protected static function booted()
    {
        static::creating(function ($otp) {
            $otp->id = (string) Str::uuid(); // Generate UUID as the ID
        });
    }

    // Relationship: OTP belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope to find valid (unused + not expired) OTPs
    public function scopeValid(Builder $query)
    {
        return $query->whereNull('verified_at')->where('expires_at', '>', Carbon::now());
    }

    // Generate a new OTP
    public static function generateOtp($user, OtpType $type)
    {
        return self::create([
            'user_id' => $user->id,
            'code' => random_int(100000, 999999),
            'type' => $type->value,
            'expires_at' => Carbon::now()->addMinutes(15), // Expires in 10 mins
            'verified_at' => null,
        ]);
    }

    // Verify OTP and mark it as used
    public static function verifyOtp($user, string $inputCode)
    {
        if (!$user) {
            throw OtpException::expiredOrInvalid();
        }
        $otp = self::valid()
            ->where('user_id', $user->id)
            ->where('code', $inputCode)
            ->first();

        if (!$otp) {
            throw OtpException::expiredOrInvalid();
        }

        $otp->update(['verified_at' => Carbon::now()]); // Mark OTP as verified
        return $otp;
    }
}
