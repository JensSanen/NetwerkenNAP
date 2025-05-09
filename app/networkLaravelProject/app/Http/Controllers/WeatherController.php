<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class WeatherController extends Controller
{
    private $client;
    private $positionStackApiKey;
    private $tomorrowIoApiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->positionStackApiKey = "6efcdb46e4acb8118c7fb96c5ec54d32";
        $this->tomorrowIoApiKey = "odZ1Fe5E5D6WB1SfSi4QWQcre4scEIwX";
    }

    // Stap 1: Verkrijg de coördinaten op basis van het adres
    public function getCoordinatesFromAddress($address)
    {
        // return ['latitude' => 50.8432, 'longitude' => 4.3718]; // For testing -> Max 100 requests per month
        $url = 'http://api.positionstack.com/v1/forward';
        try {
            Log::info("Fetching coordinates for address: $address");
            $response = $this->client->request('GET', $url, [
                'query' => [
                    'access_key' => $this->positionStackApiKey,
                    'query' => $address
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            Log::info('PositionStack API response: ', $data);

            if (isset($data['data'][0])) {
                $latitude = round($data['data'][0]['latitude'], 4);
                $longitude = round($data['data'][0]['longitude'], 4);
                Log::info("Coordinates found: Latitude: $latitude, Longitude: $longitude");
                return ['latitude' => $latitude, 'longitude' => $longitude];
            }


            Log::warning("No coordinates found for address: $address");
            return null;
        } catch (\Exception $e) {
            Log::error('Error in getCoordinatesFromAddress: ' . $e->getMessage());
            return null;
        }
    }

    // Stap 2: Verkrijg het weerbericht op basis van de coördinaten
    public function getWeatherByCoordinates($latitude, $longitude)
    {
        $url = 'https://api.tomorrow.io/v4/weather/forecast';
        try {
            Log::info("Fetching weather for coordinates: Latitude: $latitude, Longitude: $longitude");
            $response = $this->client->request('GET', $url, [
                'query' => [
                    'apikey' => $this->tomorrowIoApiKey,
                    'location' => "$latitude,$longitude",
                    'units' => 'metric',
                    'timesteps' => '1d',
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            Log::info("Weather data retrieved: ", $data);

            $weather_data = [];
            if (isset($data['timelines']['daily'][0])) {
                for ($i = 0; $i < count($data['timelines']['daily']); $i++) {
                    $day = $data['timelines']['daily'][$i]['time'];
                    $temperature = $data['timelines']['daily'][$i]['values']['temperatureMax'];
                    $precipitationProbabilityMax = $data['timelines']['daily'][$i]['values']['precipitationProbabilityMax'];
                    $rainAccumulationMax = $data['timelines']['daily'][$i]['values']['rainAccumulationMax'];
                    $windSpeedMax = $data['timelines']['daily'][$i]['values']['windSpeedMax'];
                    $weather_data[] = [
                        'day' => $day,
                        'temperature' => $temperature,
                        'precipitationProbability' => $precipitationProbabilityMax,
                        'rainAccumulation' => $rainAccumulationMax,
                        'windSpeed' => $windSpeedMax
                    ];
                }
            }

            return $weather_data;
        } catch (\Exception $e) {
            Log::error("Error in getWeatherByCoordinates: " . $e->getMessage());
            return null;
        }
    }

    // Combineer de twee stappen
    public function getWeatherForecast($address)
    {
        Log::info("Fetching weather forecast for address: $address");
        $coordinates = $this->getCoordinatesFromAddress($address);

        if ($coordinates) {
            $weatherData = $this->getWeatherByCoordinates($coordinates['latitude'], $coordinates['longitude']);
            Log::info("Weather forecast data: ", $weatherData);
            return $weatherData;
        }

        Log::warning("Could not retrieve weather forecast for address: $address");
        return null;
    }
}
