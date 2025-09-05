<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getWeather(float $lat, float $lon): ?array
    {
        $cacheKey = "weather:$lat,$lon";

        return Cache::remember($cacheKey, 3600, function() use ($lat, $lon) {
            $url = "https://api.open-meteo.com/v1/forecast";

            $response = Http::get($url, [
                'latitude' => $lat,
                'longitude' => $lon,
                'daily' => 'temperature_2m_mean',
                'current_weather' => true,
                'timezone' => 'auto',
                'past_days' => 10,
                'forecast_days' => 0,
            ]);

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        });
    }

    public function calculateTrend(array $daily): ?float
    {
        if (empty($daily['temperature_2m_mean'])) {
            return null;
        }

        $temps = $daily['temperature_2m_mean'];
        return array_sum($temps) / count($temps);
    }
}
