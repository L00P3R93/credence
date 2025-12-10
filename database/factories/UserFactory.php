<?php

namespace Database\Factories;

use App\Faker\Providers\KenyaProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (\App\Models\User $user) {
            //
        })->afterCreating(function (\App\Models\User $user) {
            //
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create();
        $faker->addProvider(new KenyaProvider($faker));

        $name = $faker->name();
        $nameArr = explode(" ", $name);
        $firstName = $nameArr[0] ?? 'user';
        $lastName = $nameArr[1] ?? $faker->randomNumber(5);

        return [
            'name' => $name,
            'username' => Str::lower("{$firstName}.{$lastName}"),
            'email' => $faker->unique()->safeEmail(),
            'phone' => $faker->unique()->kenyanPhone(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => 'active',
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
}
