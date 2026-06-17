<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CardStopController extends AbstractController
{
    #[Route('/cardstop', name: 'app_card_stop')]
    public function index(): Response
    {
        return $this->render('card_stop/index.html.twig', [
            'controller_name' => 'CardStopController',
        ]);
    }
}
