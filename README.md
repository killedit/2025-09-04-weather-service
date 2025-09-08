# Weather Service Application

![alt text](/resources/images/weather-service-screenshot.png)

This application displays current temperature and a trend for a city in Celsius. It's a RESTful API with just one resource.

`http://127.0.0.1:8087/weather/{CITY}`

It makes calls to two different open APIs. First one is necessary to obtain the latitude and longitude of the city and pass then to the second API which does not support picking a city by name.

I obtain the current temperature and the median temperature for the previous ten days.

All these values are stored in Redis for 1h to reduce uncessary calls to the open APIs.

## Technical aspects of the task

For this task no database is needed. Still Laravel fails if none is set so I have defined in memory SQLite in the environment settings.

Download and build the project:

```
git clone https://github.com/killedit/2025-09-04-weather-service
cd 2025-09-04-weather-service
docker compose up -d --build
```

You can explore the application at `http://127.0.0.1:8087/weather/{Sofia}`.

### Docker containers:

1. Laravel - here lives my application. The easiest way to explore what is stored in the cache is attaching to it:

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
2. Nginx - of course not needed at the moment for this small application, but if this application is to reach production it will be needed to deal with a high number of concurent connections, load balancing, caching, serving static content quickly.

3. Redis - provides in-memory caching and avoids repeatative API calls. It's easier to set-up than memcached with Laravel.

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
Then we can check the TTL of the cache.
```
ttl "laravel-database-laravel-cache-weather:42.69751,23.32415"
(integer) 2961
```
## Ideas for improvement

I have decided not to deal with this since it will require some additional thought.

There is Warsaw, Poland (capital).
And Warsaw, USA..
1. Warsaw, Indiana
2. Warsaw, Missouri
3. Warsaw, New York
4. Warsaw, North Carolina

I couldn't find an API where I can filter cities by country, postal code or country ISO code:

https://geocoding-api.open-meteo.com/v1/search?name=Sofia&count=1 

Filters do not work:

- &country=Bulgaria
- &country_code=BG
- &postcodes=1000
- &country_id=732800

What's more if I decide to loop over the results and select a city by country what happens when there are two cites called `Sofia` in `Moldova` for example? Same country_code, country, timezone:

https://geocoding-api.open-meteo.com/v1/search?name=Sofia 

I thought of using another free tier API like `http://api.openweathermap.org/data/2.5/weather?q=sofia&appid={MY_API_KEY}&units=metric{&limit=1}`. Even without the filter it returns just one result.

## Testing
Attach to Laravel container and run `php artisan test` inside. This will run both unit and integration tests. It's faking external HTTP calls with no actual API usage. The results should look like this:
```
php artisan test

Tests:    4 passed (13 assertions)
Duration: 0.22s
```

To check test coverage:
```
vendor/bin/phpunit --coverage-html=storage/coverage

4 / 4 (100%)
Time: 00:00.317, Memory: 34.00 MB
OK (4 tests, 13 assertions)
```

