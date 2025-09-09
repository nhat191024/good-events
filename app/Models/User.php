<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

use Cmgmyr\Messenger\Traits\Messagable;

use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

use BeyondCode\Vouchers\Traits\CanRedeemVouchers;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

use App\Enum\Role;

class User extends Authenticatable implements Wallet, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles, Messagable, HasWallet, CanRedeemVouchers, LogsActivity;

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

    /**
     * Summary of getActivitylogOptions
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }

    /**
     * Determine if the user can access the Filament admin panel.
     * @param \Filament\Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin' && $this->hasRole(Role::ADMIN)) {
            return true;
        } else if ($panel->getId() === 'partner' && $this->hasRole(Role::PARTNER)) {
            return true;
        }
        return false;
    }

    /**
     * Get the URL of the user's avatar for Filament.
     * @return string|null
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar) {
            return asset($this->avatar);
        }

        return null;
    }


    //model boot method
    protected static function booted(): void
    {
        parent::boot();
        static::creating(function ($user) {
            if (empty($user->avatar)) {
                $name = urlencode($user->name);
                $user->avatar = "https://ui-avatars.com/api/?name={$name}&background=random&size=512";
            }
        });

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

    public function partnerBillsAsClient()
    {
        return $this->hasMany(PartnerBill::class, 'client_id');
    }

    public function partnerBillsAsPartner()
    {
        return $this->hasMany(PartnerBill::class, 'partner_id');
    }

    public function partnerBillsDetails()
    {
        return $this->hasMany(PartnerBillDetail::class, 'partner_id');
    }
}
