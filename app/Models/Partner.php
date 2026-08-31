<?php

namespace App\Models;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use BeyondCode\Vouchers\Models\Voucher;
use Carbon\CarbonImmutable;
use Cmgmyr\Messenger\Models\Participant;
use Codebyray\ReviewRateable\Models\Review;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
 * @property-read Wallet $wallet
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
 * @property-read Collection<int, Role> $roles
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereAppleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereCanAcceptShows($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereFcmToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereIsDeleteAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner wherePhoneVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner withoutRole($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Partner extends User
{
    use HasFactory;

    protected $table = 'users';

    protected $guard_name = 'web';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
