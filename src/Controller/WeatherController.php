<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;



#[Route('/weather')]
class WeatherController extends AbstractController
{

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/highlander-says/{threshold<\d+>?50}', host: 'api.localhost')]
    public function highlanderSaysApi(int $threshold): Response
    {
        $draw = random_int(0, 100);
        $forecast = $draw < $threshold ? "It's going to rain :-) " : "It's going to be sunny ;-)";
        $json = [
            'forecast' => $forecast,
            'self' => $this->generateUrl('app_weather_highlandersaysapi', ['threshold' => $threshold], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
        return new JsonResponse($json);
    }

    #[Route('/highlander-says/{threshold<\d+>?50}')]
    public function highlanderSays(int $threshold): Response
    {
        $draw = random_int(0, 100);
        $forecast = $draw < $threshold ? "It's going to rain :-) " : "It's going to be sunny ;-)";
        return $this->render('weather/highlander-says.html.twig', [
            'forecast' => $forecast,
        ]);
    }

    #[Route('/highlander-says/{guess}')]
    public function highlanderSaysGuess(string $guess): Response    {
       
        $forecast = "It's going to $guess :-)"
        ;
        return $this->render('weather/highlander-says.html.twig', [
            'forecast' => $forecast,
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
        $nominatimUrl = 'https://nominatim.openstreetmap.org/search';
        $response = $this->httpClient->request('GET', $nominatimUrl, [
            'query' => [
                'q' => $city_name,
                'countrycodes' => strtolower($country_code),
                'format' => 'json',
                'limit' => 1,
            ],
        ]);

        $data = $response->toArray();
        if (empty($data)) {
            return new Response('Wrong City!', Response::HTTP_BAD_REQUEST);
        }

        return $this->render('weather/highlander-forecast.html.twig', [
            'country_code' => $country_code,
            'city_name' => $city_name,
        ]);
    }

}
