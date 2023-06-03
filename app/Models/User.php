<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use App\Traits\Utils;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Authenticatable implements Wallet
{
    use HasWallet, HasApiTokens, HasFactory, Notifiable, Utils;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'qrcode' => 'object',
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function setPinAttribute($value)
    {
        $this->attributes['pin'] = Hash::make($value);
    }

    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['phone_number'] = $this->formatPhoneNumber($value);
    }

    // check if user has verified phone number
    protected function getHasVerifiedPhoneNumberAttribute()
    {
        return !is_null($this->attributes['phone_number_verified_at']);
    }
}
