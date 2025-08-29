<?php

namespace Feature\Controllers;

use App\Models\MinecraftWorld;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetWorldGenSettingsController extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $instanceId = 'ec2_s83mcjaoe';
        $expectedOptions = [
            'chunky_radius' => 500,
            'chunky_shape'  => 'square',
        ];

        MinecraftWorld::factory()->createQuietly([
            'name'      => 'Test World',
            'server_id' => Server::factory()->createQuietly([
                'ec2_instance_id' => $instanceId
            ])->getKey(),
            'user_id'   => User::factory()->create()->getKey(),
            'options'   => $expectedOptions,
        ]);

        $response = $this->get("/api/$instanceId");

        $response->assertExactJson($expectedOptions);
    }
}
