<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoService
{
    public function getCoordinates(string $city): ?array
    {
        $city = ucfirst(strtolower($city));
        $cacheKey = "geo:$city";

        return Cache::remember($cacheKey, 3600, function() use ($city) {
            $response = Http::get("https://geocoding-api.open-meteo.com/v1/search", [
                'name' => $city,
                'count' => 1,
            ]);

            if ($response->failed() || empty($response['results'][0])) {
                return null;
            }

            $result = $response['results'][0];
            return [
                'lat' => $result['latitude'],
                'lon' => $result['longitude'],
            ];
        });
    }
}
