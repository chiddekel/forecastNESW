<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\HighlanderApiDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


#[Route('/weather')]
class WeatherController extends AbstractController
{

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/highlander-says/api')]
    public function highlanderSaysApi(
        #[MapQueryString] ?HighlanderApiDTO $dto = null
    ): Response {

        if ($dto === null) {
            $dto = new HighlanderApiDTO();
            $dto->threshold = 50;
            $dto->trials = 1;
        }

        for ($i = 0; $i < $dto->trials; $i++) {

            $draw = random_int(0, 100);
            $forecast = $draw < $dto->threshold ? "It's going to rain :-) " : "It's going to be sunny ;-)";
            $forecasts[] = $forecast;
        }


        $json = [
            'forecasts' => $forecasts,
            'threshold' => $dto->threshold,
            'self' => $this->generateUrl('app_weather_highlandersaysapi', ['threshold' => $dto->threshold], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        return $this->json($json);

        // Alternative: return new JsonResponse($json);
        // Alternative: return file as response
        // return $this->file(
        //     __DIR__ . '/ideaspot-tv.png',
        //     'logo.png',
        //     ResponseHeaderBag::DISPOSITION_INLINE
        // );

    }

    #[Route('/highlander-says/{threshold<\d+>}')]
    public function highlanderSays(
        Request $request,
        RequestStack $requestStack,
        ?int $threshold = null,
    ): Response {
        $session = $requestStack->getSession();
        if ($threshold) {
            $session->set('threshold', $threshold);
            $this->addFlash(type: 'info', message: "You have set the treshold to $threshold");
        } else {
            $threshold = $session->get('threshold', 50);
        }
        $trials = $request->query->get('trials', 1);

        $forecasts = [];

        for ($i = 0; $i < $trials; $i++) {

            $draw = random_int(0, 100);
            $forecast = $draw < $threshold ? "It's going to rain :-) " : "It's going to be sunny ;-)";
            $forecasts[] = $forecast;
        }

        return $this->render('weather/highlander-says.html.twig', [
            'forecasts' => $forecasts,
            'threshold' => $threshold,
        ]);
    }

    #[Route('/highlander-says/{guess}')]
    public function highlanderSaysGuess(string $guess): Response
    {

        $availableGuess = ['snow', 'rain', 'hail'];


        if (!in_array($guess, $availableGuess)) {
            throw $this->createNotFoundException('This guest is not found');
            // throw new NotFoundHttpException('This guest is not found (manually)');
            // throw new BadRequestHttpException("Bad request");
            // throw new Exception('Bad exception');
        }

        $forecast = "It's going to $guess :-)";
        return $this->render('weather/highlander-says.html.twig', [
            'forecasts' => [$forecast],
        ]);
    }

    // #[Route('/{country_code<AF|AX|AL|DZ|AS|AD|AO|AI|AQ|AG|AR|AM|AW|AU|AT|AZ|BS|BH|BD|BB|BY|BE|BZ|BJ|BM|BT|BO|BA|BW|BV|BR|IO|BN|BG|BF|BI|CV|KH|CM|CA|KY|CF|TD|CL|CN|CX|CC|CO|KM|CG|CD|CK|CR|CI|HR|CU|CY|CZ|DK|DJ|DM|DO|EC|EG|SV|GQ|ER|EE|ET|FK|FO|FJ|FI|FR|GF|PF|TF|GA|GM|GE|DE|GH|GI|GR|GL|GD|GP|GU|GT|GG|GN|GW|GY|HT|HM|VA|HN|HK|HU|IS|IN|ID|IR|IQ|IE|IM|IL|IT|JM|JP|JE|JO|KZ|KE|KI|KP|KR|KW|KG|LA|LV|LB|LS|LR|LY|LI|LT|LU|MO|MG|MW|MY|MV|ML|MT|MH|MQ|MR|MU|YT|MX|FM|MD|MC|MN|ME|MS|MA|MZ|MM|NA|NR|NP|NL|NC|NZ|NI|NE|NG|NU|NF|MP|NO|OM|PK|PW|PS|PA|PG|PY|PE|PH|PN|PL|PT|PR|QA|RE|RO|RU|RW|BL|SH|KN|LC|MF|PM|VC|WS|SM|ST|SA|SN|RS|SC|SL|SG|SX|SK|SI|SB|SO|ZA|GS|SS|ES|LK|SD|SR|SJ|SE|CH|SY|TW|TJ|TZ|TH|TL|TG|TK|TO|TT|TN|TR|TM|TC|TV|UG|UA|AE|GB|US|UM|UY|UZ|VU|VE|VN|VG|VI|WF|EH|YE|ZM|ZW>}/{city_name}')]
    // public function forecast(string $country_code, string $city_name)   
    // {
    //     $validCities = ['Barcelona', 'Paris', 'Berlin'];
    //     if (!in_array($city_name, $validCities)) {
    //         return new Response('Wrong City!', Response::HTTP_BAD_REQUEST);
    //     }

    //     return $this->render('weather/highlander-forecast.html.twig', [
    //         'country_code' => $country_code,
    //         'city_name' => $city_name,
    //     ]);
    // }

    #[Route('/{country_code<AF|AX|AL|DZ|AS|AD|AO|AI|AQ|AG|AR|AM|AW|AU|AT|AZ|BS|BH|BD|BB|BY|BE|BZ|BJ|BM|BT|BO|BA|BW|BV|BR|IO|BN|BG|BF|BI|CV|KH|CM|CA|KY|CF|TD|CL|CN|CX|CC|CO|KM|CG|CD|CK|CR|CI|HR|CU|CY|CZ|DK|DJ|DM|DO|EC|EG|SV|GQ|ER|EE|ET|FK|FO|FJ|FI|FR|GF|PF|TF|GA|GM|GE|DE|GH|GI|GR|GL|GD|GP|GU|GT|GG|GN|GW|GY|HT|HM|VA|HN|HK|HU|IS|IN|ID|IR|IQ|IE|IM|IL|IT|JM|JP|JE|JO|KZ|KE|KI|KP|KR|KW|KG|LA|LV|LB|LS|LR|LY|LI|LT|LU|MO|MG|MW|MY|MV|ML|MT|MH|MQ|MR|MU|YT|MX|FM|MD|MC|MN|ME|MS|MA|MZ|MM|NA|NR|NP|NL|NC|NZ|NI|NE|NG|NU|NF|MP|NO|OM|PK|PW|PS|PA|PG|PY|PE|PH|PN|PL|PT|PR|QA|RE|RO|RU|RW|BL|SH|KN|LC|MF|PM|VC|WS|SM|ST|SA|SN|RS|SC|SL|SG|SX|SK|SI|SB|SO|ZA|GS|SS|ES|LK|SD|SR|SJ|SE|CH|SY|TW|TJ|TZ|TH|TL|TG|TK|TO|TT|TN|TR|TM|TC|TV|UG|UA|AE|GB|US|UM|UY|UZ|VU|VE|VN|VG|VI|WF|EH|YE|ZM|ZW>}/{city_name}', name: 'forecast')]
    public function forecast(string $country_code, string $city_name): Response
    {
        $actualCityName = $city_name;
        
        // Only validate for Poland (PL), other countries use dummy data
        if (strtoupper($country_code) === 'PL') {

        try {
            // Validate city/village exists in Poland using Nominatim (OpenStreetMap - Free API)
            $nominatimUrl = 'https://nominatim.openstreetmap.org/search';
            $response = $this->httpClient->request('GET', $nominatimUrl, [
                'headers' => [
                    'User-Agent' => 'SymfonyWeatherApp/1.0',
                ],
                'query' => [
                    'q' => $city_name,
                    'countrycodes' => 'pl',
                    'format' => 'json',
                    'limit' => 5,
                    'addressdetails' => 1,
                    'accept-language' => 'pl',
                ],
            ]);

            $data = $response->toArray();
            
            // Check if location was found
            if (empty($data)) {
                return $this->render('weather/error-modal.html.twig', [
                    'error_title' => '🔍 Nie znaleziono lokalizacji',
                    'error_message' => "Miejscowość <strong>{$city_name}</strong> nie istnieje w Polsce!<br><small>Sprawdź pisownię lub spróbuj innej nazwy.</small>",
                    'input_value' => $city_name,
                    'country_code' => $country_code,
                    'suggestions' => [
                        'Warszawa', 'Kraków', 'Gdańsk', 'Wrocław', 'Poznań', 'Łódź'
                    ],
                ]);
            }
        } catch (\Exception $e) {
            return $this->render('weather/error-modal.html.twig', [
                'error_title' => '⚠️ Błąd API',
                'error_message' => "Wystąpił problem podczas sprawdzania lokalizacji.<br><small>Spróbuj ponownie za chwilę.</small>",
                'input_value' => $city_name,
                'country_code' => $country_code,
            ]);
        }

        // Verify location is actually in Poland and extract city name
        $validPolishLocation = false;
        $actualCityName = $city_name;
        $latitude = null;
        $longitude = null;
        
        // Mapping of Polish voivodeships to their capitals
        $voivodeshipCapitals = [
            'województwo dolnośląskie' => 'Wrocław',
            'województwo kujawsko-pomorskie' => 'Bydgoszcz',
            'województwo lubelskie' => 'Lublin',
            'województwo lubuskie' => 'Gorzów Wielkopolski',
            'województwo łódzkie' => 'Łódź',
            'województwo małopolskie' => 'Kraków',
            'województwo mazowieckie' => 'Warszawa',
            'województwo opolskie' => 'Opole',
            'województwo podkarpackie' => 'Rzeszów',
            'województwo podlaskie' => 'Białystok',
            'województwo pomorskie' => 'Gdańsk',
            'województwo śląskie' => 'Katowice',
            'województwo świętokrzyskie' => 'Kielce',
            'województwo warmińsko-mazurskie' => 'Olsztyn',
            'województwo wielkopolskie' => 'Poznań',
            'województwo zachodniopomorskie' => 'Szczecin',
        ];
        
        foreach ($data as $location) {
            if (isset($location['address']['country_code']) && 
                strtoupper($location['address']['country_code']) === 'PL') {
                $validPolishLocation = true;
                
                // Extract latitude and longitude for weather API
                $latitude = $location['lat'] ?? null;
                $longitude = $location['lon'] ?? null;
                
                // Extract actual city/village name from API response
                $address = $location['address'];
                $addressType = $location['addresstype'] ?? '';
                
                // Priority order for extracting city names
                if (isset($address['city'])) {
                    $actualCityName = $address['city'];
                } elseif (isset($address['town'])) {
                    $actualCityName = $address['town'];
                } elseif (isset($address['village'])) {
                    $actualCityName = $address['village'];
                } elseif (isset($address['municipality'])) {
                    $actualCityName = $address['municipality'];
                } 
                // Handle districts/neighborhoods - use parent city
                elseif (in_array($addressType, ['neighbourhood', 'quarter', 'suburb', 'city_district'])) {
                    if (isset($address['city'])) {
                        $actualCityName = $address['city'];
                    } elseif (isset($address['town'])) {
                        $actualCityName = $address['town'];
                    }
                }
                // Handle streets/roads - use parent city
                elseif ($addressType === 'road') {
                    if (isset($address['city'])) {
                        $actualCityName = $address['city'];
                    } elseif (isset($address['town'])) {
                        $actualCityName = $address['town'];
                    } elseif (isset($address['village'])) {
                        $actualCityName = $address['village'];
                    }
                }
                // Handle voivodeships - use capital city
                elseif (isset($address['state']) && $addressType === 'state') {
                    $stateName = strtolower($address['state']);
                    if (isset($voivodeshipCapitals[$stateName])) {
                        $actualCityName = $voivodeshipCapitals[$stateName];
                    }
                }
                // Handle counties - extract city name from county name
                elseif ($addressType === 'county' && isset($address['county'])) {
                    // Extract city from "powiat X" format
                    $countyName = $address['county'];
                    if (preg_match('/powiat\s+(.+)/i', $countyName, $matches)) {
                        $cityFromCounty = trim($matches[1]);
                        // Capitalize first letter
                        $actualCityName = mb_convert_case($cityFromCounty, MB_CASE_TITLE, 'UTF-8');
                    }
                }
                
                break;
            }
        }

        if (!$validPolishLocation) {
            return $this->render('weather/error-modal.html.twig', [
                'error_title' => '🌍 Lokalizacja poza Polską',
                'error_message' => "Lokalizacja <strong>{$city_name}</strong> nie znajduje się w Polsce!<br><small>Walidacja działa tylko dla Polski.</small>",
                'input_value' => $city_name,
                'country_code' => $country_code,
            ]);
        }
            // Get real weather data from Open-Meteo API (free, no key needed)
            if ($latitude && $longitude) {
                try {
                    $weatherApiUrl = 'https://api.open-meteo.com/v1/forecast';
                    $weatherResponse = $this->httpClient->request('GET', $weatherApiUrl, [
                        'query' => [
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,cloud_cover,pressure_msl,wind_speed_10m,wind_direction_10m',
                            'timezone' => 'Europe/Warsaw',
                        ],
                    ]);

                    $weatherData = $weatherResponse->toArray();
                    $current = $weatherData['current'] ?? [];
                    
                    // Map wind direction degrees to compass
                    $windDeg = $current['wind_direction_10m'] ?? 0;
                    $windDirections = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
                    $windDir = $windDirections[round($windDeg / 45) % 8];
                    
                    // Map weather code to icon
                    $weatherCode = $current['weather_code'] ?? 0;
                    $icon = $this->getWeatherIcon($weatherCode);
                    
                    $realWeatherData = [
                        'city' => $actualCityName,
                        'datetime' => date('Y-m-d H:i'),
                        'temperature' => round($current['temperature_2m'] ?? 15),
                        'feels_like' => round($current['apparent_temperature'] ?? 15),
                        'pressure' => round($current['pressure_msl'] ?? 1013),
                        'humidity' => round($current['relative_humidity_2m'] ?? 50),
                        'wind' => round($current['wind_speed_10m'] ?? 10),
                        'wind_direction' => $windDir,
                        'cloudiness' => round($current['cloud_cover'] ?? 50),
                        'icon' => $icon,
                    ];
                } catch (\Exception $e) {
                    // Fallback to dummy data if weather API fails
                    $realWeatherData = $this->getDummyWeatherData($actualCityName);
                }
            } else {
                $realWeatherData = $this->getDummyWeatherData($actualCityName);
            }
        } else {
            // For non-PL countries, use dummy data
            $realWeatherData = $this->getDummyWeatherData($city_name);
        }
        
        // Build weather data array with real data first
        $weatherData = [
            $realWeatherData,
            // Hardcoded cities (2nd to 5th)
            [
                'city' => 'Barcelona',
                'datetime' => '2025-12-01 14:30',
                'temperature' => 22,
                'feels_like' => 20,
                'pressure' => 1013,
                'humidity' => 65,
                'wind' => 12,
                'wind_direction' => 'NE',
                'cloudiness' => 40,
                'icon' => 'sun',
            ],
            [
                'city' => 'Madrid',
                'datetime' => '2025-12-01 14:35',
                'temperature' => 18,
                'feels_like' => 16,
                'pressure' => 1015,
                'humidity' => 72,
                'wind' => 8,
                'wind_direction' => 'W',
                'cloudiness' => 75,
                'icon' => 'rain',
            ],
            [
                'city' => 'Valencia',
                'datetime' => '2025-12-01 14:40',
                'temperature' => 25,
                'feels_like' => 23,
                'pressure' => 1012,
                'humidity' => 58,
                'wind' => 15,
                'wind_direction' => 'E',
                'cloudiness' => 20,
                'icon' => 'sun',
            ],
            [
                'city' => 'Seville',
                'datetime' => '2025-12-01 14:45',
                'temperature' => 15,
                'feels_like' => 13,
                'pressure' => 1010,
                'humidity' => 85,
                'wind' => 5,
                'wind_direction' => 'S',
                'cloudiness' => 90,
                'icon' => 'haze',
            ],
        ];

        return $this->render('weather/highlander-forecast.html.twig', [
            'country_code' => $country_code,
            'city_name' => $actualCityName,
            'weather_data' => $weatherData,
        ]);
    }

    private function getDummyWeatherData(string $cityName): array
    {
        $directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
        $icons = ['sun', 'rain', 'haze'];
        
        return [
            'city' => $cityName,
            'datetime' => date('Y-m-d H:i'),
            'temperature' => random_int(10, 30),
            'feels_like' => random_int(8, 28),
            'pressure' => random_int(1000, 1020),
            'humidity' => random_int(40, 90),
            'wind' => random_int(5, 25),
            'wind_direction' => $directions[random_int(0, 7)],
            'cloudiness' => random_int(0, 100),
            'icon' => $icons[random_int(0, 2)],
        ];
    }

    private function getWeatherIcon(int $weatherCode): string
    {
        // WMO Weather interpretation codes
        // 0: Clear sky
        // 1-3: Mainly clear, partly cloudy, overcast
        // 45-48: Fog
        // 51-67: Rain (drizzle, rain, freezing rain)
        // 71-86: Snow
        // 95-99: Thunderstorm
        
        return match(true) {
            $weatherCode === 0 => 'sun',
            $weatherCode <= 3 => 'sun',
            $weatherCode >= 45 && $weatherCode <= 48 => 'haze',
            $weatherCode >= 51 && $weatherCode <= 67 => 'rain',
            $weatherCode >= 71 && $weatherCode <= 86 => 'rain',
            $weatherCode >= 95 => 'rain',
            default => 'sun',
        };
    }
}
