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

use Codebyray\ReviewRateable\Traits\ReviewRateable;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

use App\Enum\Role;
use App\Enum\StatisticType;

/**
 * @property int $id
 * @property string $name
 * @property string $avatar
 * @property string $email
 * @property string $country_code
 * @property string $phone
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read non-empty-string $balance
 * @property-read int $balance_int
 * @property-read \Bavix\Wallet\Models\Wallet $wallet
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Cmgmyr\Messenger\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Cmgmyr\Messenger\Models\Participant> $participants
 * @property-read int|null $participants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartnerBill> $partnerBillsAsClient
 * @property-read int|null $partner_bills_as_client_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartnerBill> $partnerBillsAsPartner
 * @property-read int|null $partner_bills_as_partner_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartnerBillDetail> $partnerBillsDetails
 * @property-read int|null $partner_bills_details_count
 * @property-read \App\Models\PartnerProfile|null $partnerProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartnerService> $partnerServices
 * @property-read int|null $partner_services_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Bavix\Wallet\Models\Transfer> $receivedTransfers
 * @property-read int|null $received_transfers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Codebyray\ReviewRateable\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Cmgmyr\Messenger\Models\Thread> $threads
 * @property-read int|null $threads_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Bavix\Wallet\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Bavix\Wallet\Models\Transfer> $transfers
 * @property-read int|null $transfers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \BeyondCode\Vouchers\Models\Voucher> $vouchers
 * @property-read int|null $vouchers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Bavix\Wallet\Models\Transaction> $walletTransactions
 * @property-read int|null $wallet_transactions_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements Wallet, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles, Messagable, HasWallet, CanRedeemVouchers, ReviewRateable, LogsActivity;

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
    public function statistics()
    {
        return $this->hasMany(Statistical::class, 'user_id');
    }

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
