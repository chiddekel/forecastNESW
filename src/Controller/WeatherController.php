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
        #[MapRequestPayload] ?HighlanderApiDTO $dto = null
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

        return new JsonResponse($json);
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
