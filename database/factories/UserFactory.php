<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Assign one of the portal roles to the generated user.
     */
    public function withRole(string $role): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            $user->assignRole($role);
        });
    }

    public function admin(): static
    {
        return $this->withRole(Roles::ADMIN);
    }

    public function teacher(): static
    {
        return $this->withRole(Roles::TEACHER);
    }

    public function guardian(): static
    {
        return $this->withRole(Roles::GUARDIAN);
    }

    public function student(): static
    {
        return $this->withRole(Roles::STUDENT);
    }
}
