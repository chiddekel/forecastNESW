<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MainController extends AbstractController
{

    #[Route('/')] #renabled and rember of premanet to not set to true or disable cache memory

    public function main(): Response
    {
        return $this->render('main.html.twig');
    }
}
