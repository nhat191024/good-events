<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'avatar',
        'email',
        'country_code',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //model boot method
    protected static function booted(): void
    {
        static::deleting(function ($user) {
            $user->partnerProfile()->delete();
            $user->partnerServices()->delete();
        });

        static::restoring(function ($user) {
            $user->partnerProfile()->restore();
            $user->partnerServices()->restore();
        });
    }

    //model relationships
    public function partnerProfile()
    {
        return $this->hasOne(PartnerProfile::class);
    }

    public function partnerServices()
    {
        return $this->hasMany(PartnerService::class);
    }
}
