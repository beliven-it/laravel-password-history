<?php

namespace Beliven\PasswordHistory\Database\Factories;

use Beliven\PasswordHistory\Models\PasswordHash;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModelFactory extends Factory
{
    protected $model = PasswordHash::class;

    public function definition()
    {
        return [
            'hash' => $this->faker->password,
        ];
    }
}
