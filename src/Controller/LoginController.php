<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Psr\Log\LoggerInterface;

class LoginController extends AbstractController
{
    #[Route(path: '/', name: 'app_welcome')]
    public function index(Request $request, LoggerInterface $logger): Response
    {
        // Check that the user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get the user object
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN'))
        {
            $logger->info("Redirecting to Admin\DashboardController::index for user: " . $user->getEmail());
            return $this->redirectToRoute('admin');
        }
        else if ($this->isGranted('ROLE_CARD_STOP'))
        {
            $logger->info("Redirecting to CardStop\CardStopDashboardController::index for user: " . $user->getEmail());
            return $this->redirectToRoute('cardstop');
        }
        else
        {
            $logger->info("Redirecting to Player\PlayerDashboardController::index for user: " . $user->getEmail());
            return $this->redirectToRoute('player');
            //eturn $this->render("home/index.html.twig", ['user' => $user]);
        }
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
