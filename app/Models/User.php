<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enum\Role;
use App\Interfaces\MustVerifyPhone;
use App\Models\Traits\MustVerifyPhoneTrait;
use Bavix\Wallet\Interfaces\Confirmable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\HasWallet;
use BeyondCode\Vouchers\Models\Voucher;
use BeyondCode\Vouchers\Traits\CanRedeemVouchers;
use Carbon\CarbonImmutable;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Traits\Messagable;
use Codebyray\ReviewRateable\Models\Review;
use Codebyray\ReviewRateable\Traits\ReviewRateable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $avatar
 * @property string $email
 * @property string $country_code
 * @property string|null $bio
 * @property string $phone
 * @property string $password
 * @property CarbonImmutable|null $email_verified_at
 * @property string|null $phone_verified_at
 * @property bool $can_accept_shows
 * @property string|null $google_id
 * @property string|null $apple_id
 * @property string|null $fcm_token
 * @property bool $is_delete_account
 * @property string|null $ban_reason
 * @property CarbonImmutable|null $deleted_at
 * @property string|null $remember_token
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Collection<int, Review> $authoredReviews
 * @property-read int|null $authored_reviews_count
 * @property-read string|null $avatar_image_tag
 * @property-read string|null $avatar_url
 * @property-read non-empty-string $balance
 * @property-read int $balance_int
 * @property-read string|null $partner_profile_name
 * @property-read \Bavix\Wallet\Models\Wallet $wallet
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Participant> $participants
 * @property-read int|null $participants_count
 * @property-read Collection<int, PartnerBill> $partnerBillsAsClient
 * @property-read int|null $partner_bills_as_client_count
 * @property-read Collection<int, PartnerBill> $partnerBillsAsPartner
 * @property-read int|null $partner_bills_as_partner_count
 * @property-read Collection<int, PartnerBillDetail> $partnerBillsDetails
 * @property-read int|null $partner_bills_details_count
 * @property-read PartnerProfile|null $partnerProfile
 * @property-read Collection<int, PartnerServiceArea> $partnerServiceAreas
 * @property-read int|null $partner_service_areas_count
 * @property-read Collection<int, PartnerService> $partnerServices
 * @property-read int|null $partner_services_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Transfer> $receivedTransfers
 * @property-read int|null $received_transfers_count
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, Statistical> $statistics
 * @property-read int|null $statistics_count
 * @property-read Collection<int, Thread> $threads
 * @property-read int|null $threads_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read Collection<int, Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read Collection<int, Transfer> $transfers
 * @property-read int|null $transfers_count
 * @property-read Collection<int, Voucher> $vouchers
 * @property-read int|null $vouchers_count
 * @property-read Collection<int, Transaction> $walletTransactions
 * @property-read int|null $wallet_transactions_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAppleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCanAcceptShows($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFcmToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsDeleteAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements Confirmable, FilamentUser, HasAvatar, HasMedia, MustVerifyEmail, MustVerifyPhone, Wallet
{
    /** @use HasFactory<UserFactory> */
    use CanConfirm, CanRedeemVouchers, CanResetPassword, HasApiTokens, HasFactory, HasRoles, HasWallet, InteractsWithMedia, LogsActivity, Messagable, MustVerifyPhoneTrait, Notifiable, ReviewRateable, SoftDeletes;

    public function pushDevices(): HasMany
    {
        return $this->hasMany(PushDevice::class, 'user_id');
    }

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
        'bio',
        'phone',
        'password',
        'can_accept_shows',
        'google_id',
        'apple_id',
        'fcm_token',
        'is_delete_account',
        'ban_reason',
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
     * Additional attributes to append to array / JSON representations.
     *
     * @var list<string>
     */
    protected $appends = [
        'avatar_url',
        'partner_profile_name',
        'avatar_image_tag',
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
            'can_accept_shows' => 'boolean',
            'is_delete_account' => 'boolean',
        ];
    }

    /**
     * Summary of getActivitylogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin' && $this->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::ADMIN,
            Role::HUMAN_RESOURCE_MANAGER,
            Role::DESIGN_MANAGER,
            Role::RENTAL_MANAGER,
            Role::BLOG_MANAGER,
        ])) {
            return true;
        } elseif ($panel->getId() === 'partner' && $this->hasRole(Role::PARTNER)) {
            return true;
        }

        return false;
    }

    /**
     * Get the URL of the user's avatar for Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('avatar', 'avatar_webp') ?: $this->avatar_url;
    }

    /**
     * Render the avatar as an HTML img tag with lazy loading and cover styling.
     */
    public function getAvatarImageTag(): ?string
    {
        $image = $this->getFirstMedia('avatar');
        if (! $image) {
            return null;
        }

        return $image
            ->img('avatar_webp')
            ->attributes([
                'class' => 'w-full h-full object-cover lazy-image',
                'loading' => 'lazy',
                'alt' => $this->name,
            ])
            ->toHtml();
    }

    /**
     * Accessor for serialized avatar image tag.
     */
    public function getAvatarImageTagAttribute(): ?string
    {
        return $this->getAvatarImageTag();
    }

    /**
     * Accessor for the user's avatar URL that supports both stored paths and external URLs.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->hasMedia('avatar')) {
            return $this->getFirstMediaUrl('avatar');
        }

        if (empty($this->avatar)) {
            return null;
        }

        if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
            return $this->avatar;
        }

        // return asset('storage/' . ltrim($this->avatar, '/'));

        $url = env('APP_ENV') === 'production' ? asset(ltrim($this->avatar, '/')) : asset('storage/'.ltrim($this->avatar, '/'));

        return $url;
    }

    /**
     * Accessor to expose the partner profile name (if any) to front-end consumers.
     */
    public function getPartnerProfileNameAttribute(): ?string
    {
        return $this->partnerProfile?->partner_name;
    }

    // model boot method
    protected static function booted(): void
    {
        parent::boot();
        static::creating(function ($user) {
            $name = urlencode($user->name);
            $user->avatar = "https://ui-avatars.com/api/?name={$name}&background=random&size=512";
        });

        static::updating(function ($user) {
            if ($user->isDirty('name')) {
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

    /*
    * Register the media collections for the model.
    */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('avatar')
            ->useDisk('public');
    }

    /**
     * Summary of registerMediaConversions
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('avatar_webp')
            ->width(400)
            ->height(400)
            ->format('webp')
            ->performOnCollections('avatar')
            ->optimize()
            ->queued();
    }

    // model relationships
    public function statistics()
    {
        return $this->hasMany(Statistical::class, 'user_id');
    }

    public function partnerProfile()
    {
        return $this->hasOne(PartnerProfile::class, 'user_id');
    }

    public function partnerServices()
    {
        return $this->hasMany(PartnerService::class, 'user_id');
    }

    public function partnerServiceAreas()
    {
        return $this->hasMany(PartnerServiceArea::class, 'user_id');
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

    /**
     * Reviews authored by this user (as the writer), not the reviews received about this user.
     */
    public function authoredReviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }
}
