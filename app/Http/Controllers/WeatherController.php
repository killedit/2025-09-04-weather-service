<?php

namespace App\Http\Controllers;

use App\Services\GeoService;
use App\Services\WeatherService;

class WeatherController extends Controller
{
    protected GeoService $geo;
    protected WeatherService $weather;

    public function __construct(GeoService $geo, WeatherService $weather)
    {
        $this->geo = $geo;
        $this->weather = $weather;
    }

    public function show(string $city)
    {
        $geo = app(\App\Services\GeoService::class);
        $geoData = $geo->getCoordinates($city);

        if (!$geoData) {

            if (request()->wantsJson()) {
                return response()->json(['error' => 'City not found'], 404);
            }
            return view('weather.show', ['error' => 'City not found']);
        }

        $weather = app(\App\Services\WeatherService::class);
        $weatherData = $weather->getWeather($geoData['lat'], $geoData['lon']);

// dd($data);
        $current = $weatherData['current_weather']['temperature'];
        $avg = $weather->calculateTrend($weatherData['daily']);

        if($current > $avg) {
            $sign = "🥵";
        } elseif( $current > $avg ) {
            $sign = "🥶";
        } else {
            $sign = "~";
        }

        $data = [
            'city' => ucfirst($city),
            'country' => ucfirst($geoData['country']),
            'temperature' => $current,
            'sign' => $sign,
        ];

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return view('weather.show', $data);
    }
}
