<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trx extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'object'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');   
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');   
    }
}
