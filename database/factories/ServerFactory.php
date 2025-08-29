<?php

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'   => $this->faker->word(),
            'status' => Server::STATUS_PENDING
        ];
    }
}
