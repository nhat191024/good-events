<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Location;
use App\Models\PartnerCategory;

use App\Enum\Role;
use App\Enum\PartnerServiceStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'country_code' => '+84',
            'phone' => fake()->unique()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a user with a specific role.
     */
    public function withRole(Role $role): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            $user->assignRole($role);
        });
    }

    /**
     * Create a partner user.
     */
    public function createPartner(): static
    {
        $locationIds = Location::pluck('id')->toArray();
        $categoryIds = PartnerCategory::whereNull('parent_id')->pluck('id')->toArray();
        return $this->afterCreating(function (User $user) use ($locationIds, $categoryIds) {
            $user->partnerProfile()->create([
                'partner_name' => fake()->name(),
                'identity_card_number' => fake()->unique()->numerify('###########'),
                'location_id' => fake()->randomElement($locationIds),
            ]);

            $usedCategoryIds = [];
            $services = collect(['Service A', 'Service B'])->map(function ($service) use ($categoryIds, &$usedCategoryIds) {
                $availableIds = array_diff($categoryIds, $usedCategoryIds);
                $categoryId = fake()->randomElement($availableIds);
                $usedCategoryIds[] = $categoryId;
                return [
                    'category_id' => $categoryId,
                    'status' => PartnerServiceStatus::APPROVED,
                ];
            })->toArray();

            $user->partnerServices()->createMany($services);
        });
    }
}
