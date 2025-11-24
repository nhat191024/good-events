<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Partner;
use App\Models\Customer;
use App\Enum\Role;

class PolymorphicUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $user = parent::retrieveById($identifier);

        if (!$user) {
            return null;
        }

        return $this->morphToCorrectModel($user);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $user = parent::retrieveByToken($identifier, $token);

        if (!$user) {
            return null;
        }

        return $this->morphToCorrectModel($user);
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $user = parent::retrieveByCredentials($credentials);

        if (!$user) {
            return null;
        }

        return $this->morphToCorrectModel($user);
    }

    /**
     * Morph the User instance to the correct model type based on roles.
     */
    protected function morphToCorrectModel(Authenticatable $user): Authenticatable
    {
        $userId = $user->getAuthIdentifier();

        // Check roles from database directly to determine correct model type
        $roles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $userId)
            ->whereIn('model_has_roles.model_type', [
                User::class,
                Partner::class,
                Customer::class,
            ])
            ->pluck('roles.name')
            ->toArray();

        if (in_array(Role::PARTNER->value, $roles)) {
            return Partner::find($userId) ?? $user;
        }

        if (in_array(Role::CLIENT->value, $roles)) {
            return Customer::find($userId) ?? $user;
        }

        return $user;
    }
}
