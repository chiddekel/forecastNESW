<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class WeatherController extends AbstractController
{

    #[Route('/weather/highlander-says/{threshold}', requirements: ['threshold' => '\d+'])]
    public function highlanderSays(int $threshold): Response
    {
        $draw = random_int(0, 100);
        $forecast = $draw < $threshold ? "It's going to rain :-) " : "It's going to be sunny ;-)";
        return $this->render('weather/highlander-says.html.twig', [
            'forecast' => $forecast,
        ]);
    }

    #[Route('/weather/highlander-says/{guess}')]
    public function highlanderSaysGuess(string $guess): Response    {
       
        $forecast = "It's going to $guess :-)"
        ;
        return $this->render('weather/highlander-says.html.twig', [
            'forecast' => $forecast,
        ]);
    }

}
