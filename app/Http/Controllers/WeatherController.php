<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    public function get()
    {

        $cacheKey = 'weather_data_bandung';
        $cacheDuration = 3600; // 1 jam dalam detik

        $weatherData = Cache::remember($cacheKey, $cacheDuration, function () {
            $apiKey = env('OPENWEATHER_API_KEY');
            
            if (!$apiKey) {
                return ['error' => 'API Key tidak diatur di server.'];
            }

            $lat = -6.9175; // Latitude Bandung
            $lon = 107.6191; // Longitude Bandung
            $units = 'metric';
            
            $apiUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$apiKey}&units={$units}";

            $response = Http::get($apiUrl);

            if ($response->failed()) {
                return ['error' => 'Gagal mengambil data dari OpenWeatherMap.'];
            }

            $data = $response->json();

            // Saring hanya data yang kita butuhkan
            return [
                'location'  => $data['name'],
                'temperature' => round($data['main']['temp']),
                'icon'      => $data['weather'][0]['icon'],
            ];
        });

        // Kembalikan data dalam format JSON
        return response()->json($weatherData);
    }
}