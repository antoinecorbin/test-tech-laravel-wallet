<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public static function booted()
    {
        static::created(function(User $user){
            $user->wallet()->create([
                'balance' => 0,
            ]);
        });
    }

    /**
     * @return HasOne<Wallet>
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }
}
