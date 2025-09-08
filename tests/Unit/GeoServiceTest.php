<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\GeoService;

class GeoServiceTest extends TestCase
{
    public function test_get_coordinates_returns_result()
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
        ]);

        $geo = new GeoService();
        $result = $geo->getCoordinates('Sofia');

        $this->assertNotNull($result);
        $this->assertEquals('Sofia', $result['city']);
        $this->assertEquals('BG', $result['country']);
        $this->assertEquals(42.6977, $result['lat']);
        $this->assertEquals(23.3219, $result['lon']);
    }

    public function test_get_coordinates_returns_null_when_city_not_found()
    {
        Http::fake([
            'https://geocoding-api.open-meteo.com/*' => Http::response([
                'results' => []
            ], 200),
        ]);

        $geo = new GeoService();
        $result = $geo->getCoordinates('UnknownCity');

        $this->assertNull($result);
    }
}
