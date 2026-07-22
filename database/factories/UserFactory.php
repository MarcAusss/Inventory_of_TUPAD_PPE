<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The model associated with this factory.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

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

            'username' => fake()
                ->unique()
                ->userName(),

            'email' => fake()
                ->unique()
                ->safeEmail(),

            'email_verified_at' => now(),

            'password' => static::$password ??= Hash::make('password'),

            'remember_token' => Str::random(10),

            'role_id' => fn (): int => Role::query()
                ->firstOrCreate(
                    ['name' => 'Supply Unit'],
                    ['description' => 'Supply Unit user']
                )
                ->id,

            'province_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a Supply Unit user.
     */
    public function supply(): static
    {
        return $this->state(fn (): array => [
            'role_id' => $this->roleId('Supply Unit'),
            'province_id' => null,
        ]);
    }

    /**
     * Create a TSSD Unit user.
     */
    public function tssd(): static
    {
        return $this->state(fn (): array => [
            'role_id' => $this->roleId('TSSD Unit'),
            'province_id' => null,
        ]);
    }

    /**
     * Create an Accounting Unit user.
     */
    public function accounting(): static
    {
        return $this->state(fn (): array => [
            'role_id' => $this->roleId('Accounting Unit'),
            'province_id' => null,
        ]);
    }

    /**
     * Create a Provincial Office user.
     */
    public function provincial(int $provinceId): static
    {
        return $this->state(fn (): array => [
            'role_id' => $this->roleId('Provincial Office'),
            'province_id' => $provinceId,
        ]);
    }

    /**
     * Get or create a role ID.
     */
    private function roleId(string $name): int
    {
        return Role::query()
            ->firstOrCreate(
                ['name' => $name],
                ['description' => $name.' user']
            )
            ->id;
    }
}