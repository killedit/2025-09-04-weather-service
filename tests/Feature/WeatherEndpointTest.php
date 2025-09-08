<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class WeatherEndpointTest extends TestCase
{
    public function test_weather_endpoint_returns_json_for_valid_city()
    {
        Http::fake([
            'https://geocoding-api.open-meteo.com/*' => Http::response([
                'results' => [
                    [
                        'name' => 'Sofia',
                        'country' => 'BG',
                        'latitude' => 42.6977,
                        'longitude' => 23.3219,
                    ]
                ]
            ], 200),
            'https://api.open-meteo.com/*' => Http::response([
                'current_weather' => [
                    'temperature' => 22.6,
                ],
                'daily' => [
                    'temperature_2m_mean' => [
                        22.4,
                        23.9,
                        19.9,
                        20.1,
                        22.0,
                        24.0,
                        22.5,
                        21.8,
                        20.8,
                        20.6
                    ]
                ]
            ], 200),
        ]);

        $response = $this->getJson('/weather/sofia');

// dd($response);
        $response->assertStatus(200)
            ->assertJsonFragment([
            'city' => 'Sofia',
            'country' => 'BG',
            'temperature' => 22.6,
            'sign' => '🥵'
        ]);
    }

    public function test_weather_endpoint_returns_404_for_invalid_city()
    {
        Http::fake([
            'https://geocoding-api.open-meteo.com/*' => Http::response([
                'results' => []
            ], 200),
        ]);

        $response = $this->getJson('/weather/NowhereCity');

        $response->assertStatus(404)
        ->assertJson([
            'error' => 'City not found',
        ]);
    }
}
