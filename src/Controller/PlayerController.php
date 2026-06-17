<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\CardStop;
use App\Entity\PlayerLocation;
use App\Form\CheckinFormType;
use App\Message\PlayerMessage;
use App\DataFixtures\PlayerAction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;

final class PlayerController extends AbstractController
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    #[Route('/player', name: 'app_player')]
    public function index(): Response
    {
        return $this->render('home/player.html.twig', [
            'controller_name' => 'PlayerController',
        ]);
    }

    #[Route('/check-in', name: 'app_check_in')]
    public function checkIn(Request $request, CardStop $cardStop, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get the user object
        /** @var User $user */
        $user = $this->getUser();
        $new_location = new PlayerLocation();
        $new_location->setPlayer($user);
        $new_location->setCardStop($cardStop);
        $new_location->setCheckinTime(new \DateTime());

        $twig = 'home/check_in.html.twig';
        $form = $this->createForm(CheckInFormType::class, $new_location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // Save location
            $entityManager->persist($new_location);
            $user->setLocation($new_location);
            $entityManager->persist($user);
            $entityManager->flush();

            // Send message to queue for check in
            $this->messageBus->dispatch(new PlayerMessage($user->getEmail(), $user->getFirstName(), $user->getLastName(), PlayerAction::$CheckIn));

            $twig = 'home/index.html.twig';
        }

        return $this->render($twig, [
            'controller_name' => 'PlayerController',
        ]);
    }

    #[Route('/check-out', name: 'app_check_out')]
    public function checkOut(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setLocation(null);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'PlayerController',
        ]);
    }
}
