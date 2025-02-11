<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\LowBalanceNotification;
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

    /**
     * Refactoring -> Observer
     * @return void
     */
    public static function booted(): void
    {
        static::created(function(User $user){
            $user->wallet()->create([
                'balance' => 0,
            ]);
        });
    }

    public function notifyBalanceIsLow(): void
    {
        // 1000 = 10.00
        if($this->wallet->balance < 1000){
            $this->notify(new LowBalanceNotification);
        }
    }

    /**
     * @return HasOne<Wallet>
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }
}
