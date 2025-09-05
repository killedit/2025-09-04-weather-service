# Weather Service Application

This application displays current temperature and a trend for a city in Celsius. It's a RESTful API with just one resource.

`http://127.0.0.1:8087/weather/{CITY}`

It makes calls to two different open APIs. First one is necessary to obtain the latitude and longitude of the city and pass them to the second API which does not support picking a city by name.

Finally I obtain the current temperature and the median temperature for the previous ten days.

All these values are stored in Redis for 1h to reduce uncessary calls to the open APIs.

## Technical aspects of the task

For this task no database is needed. Still Laravel fails is none is set so I have defined in memory SQLite in the environment settings.

Download and build the project:

```
git clone https://github.com/killedit/2025-09-04-weather-service
cd 2025-09-04-weather-service
docker compose up -d --build
```

### Docker containers:

1. Laravel
Here lives my application. The easiest way to explore what is stored in the cache is attaching to it:
```
php artisan tinker
> Cache::get('geo:Sofia');
= [
    "lat" => 42.69751,
    "lon" => 23.32415,
  ]
> Cache::get('weather:42.69751,23.32415');
[
    "latitude" => 42.6875,
    "longitude" => 23.3125,
    "generationtime_ms" => 0.8857250213623,
    "utc_offset_seconds" => 10800,
    "timezone" => "Europe/Sofia",
    "timezone_abbreviation" => "GMT+3",
    "elevation" => 555.0,
    "current_weather_units" => [
      "time" => "iso8601",
      "interval" => "seconds",
      "temperature" => "<C2><B0>C",
      "windspeed" => "km/h",
      "winddirection" => "<C2><B0>",
      "is_day" => "",
      "weathercode" => "wmo code",
    ],
    "current_weather" => [
      "time" => "2025-09-05T23:45",
      "interval" => 900,
      "temperature" => 18.5,
      "windspeed" => 2.6,
      "winddirection" => 146,
      "is_day" => 0,
      "weathercode" => 0,
    ],
    "daily_units" => [
      "time" => "iso8601",
      "temperature_2m_mean" => "<C2><B0>C",
    ],
    "daily" => [
      "time" => [
        "2025-08-26",
        "2025-08-27",
        "2025-08-28",
        "2025-08-29",
        "2025-08-30",
        "2025-08-31",
        "2025-09-01",
        "2025-09-02",
        "2025-09-03",
        "2025-09-04",
:
```
2. Nginx - fast web server and reverse proxy, serving static files and forwarding request to Laravel.
3. Redis - provides in-memory caching and avoids repeatative API calls.
To check what happens with the cache let's attach to this container:
```
docker exec -it weather-service-redis redis-cli
select 1
keys *
```
Should result in such output:
```
1) "laravel-database-laravel-cache-weather:42.69751,23.32415"
2) "laravel-database-laravel-cache-geo:Sofia"
```
Then we can check the TTL.
```
ttl "laravel-database-laravel-cache-weather:42.69751,23.32415"
(integer) 2961
```

## Testing

