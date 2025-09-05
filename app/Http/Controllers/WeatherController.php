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
        $coordinates = $this->geo->getCoordinates($city);
        if (!$coordinates) {
            abort(404, "City not found");
        }

        $data = $this->weather->getWeather($coordinates['lat'], $coordinates['lon']);
        if (!$data) {
            abort(500, "Weather API failed");
        }

        $current = $data['current_weather']['temperature'];
        $avg = $this->weather->calculateTrend($data['daily']);

        if($current > $avg) {
            $sign = "🥵";
        } elseif( $current > $avg ) {
            $sign = "🥶";
        } else {
            $sign = "~";
        }

        return view('weather.show', [
            'city' => ucfirst($city),
            'temperature' => $current,
            'sign' => $sign,
        ]);
    }
}
