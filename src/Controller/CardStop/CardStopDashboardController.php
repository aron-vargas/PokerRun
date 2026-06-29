<?php

namespace App\Controller\CardStop;

use App\Message\PlayerMessage;
use App\DataFixtures\PlayerAction;
use App\Repository\PlayerLocationRepository;
use App\Controller\Admin\CardStopCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

#[AdminDashboard(routePath: '/cardstop', routeName: 'cardstop')]
class CardStopDashboardController extends AbstractDashboardController {
    public function __construct(private PlayerLocationRepository $playerLocationRepository, private MessageBusInterface $messageBus)
    {

    }

    #[Route('/cardstop', name: 'app_cardstop')]
    public function index(): Response
    {
        // Get the user object
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $stop_id = $user->getCardStop()->GetId();
        $pendingVerifications = $this->playerLocationRepository->findCardStopUnverified($stop_id, 50);

        return $this->render('card_stop/index.html.twig', ['user' => $user, 'pendingVerifications' => $pendingVerifications]);
    }

    #[Route('/cardstop/checkin/confirm/{location_id}/{player_id}', name: 'checkin_confirm')]
    #[AdminRoute('/checkin/confirm/{location_id}/{player_id}', name: 'checkin_confirm')]
    public function confirmCheckin(int $location_id, int $player_id): Response
    {
        // Get the user object
        /** @var User $user */
        $user = $this->getUser();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->isGranted('ROLE_CARD_STOP'))
        {
            $location = $this->playerLocationRepository->findOneById($location_id);
            $this->playerLocationRepository->verifyLocation($location, $user->getId(), true);

            // Send Notication to Player
            $this->messageBus->dispatch(new PlayerMessage($location->getPlayer(), $location, PlayerAction::$ApproveCheckin));

            //return $this->redirectToRoute('cardstop');
        }

        $stop_id = $user->getCardStop()->GetId();
        $pendingVerifications = $this->playerLocationRepository->findCardStopUnverified($stop_id, 50);

        return $this->render('card_stop/index.html.twig', [
            'controller_name' => 'CardStopDashboardController',
            'user' => $user,
            'pendingVerifications' => $pendingVerifications,
        ]);
    }

    #[Route('/cardstop/checkin/deny/{location_id}/{player_id}', name: 'checkin_deny')]
    #[AdminRoute('/checkin/deny/{location_id}/{player_id}', name: 'checkin_deny')]
    public function denyCheckin(int $location_id, int $player_id): Response
    {
        // Get the user object
        /** @var User $user */
        $user = $this->getUser();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->isGranted('ROLE_CARD_STOP'))
        {
            $location = $this->playerLocationRepository->findOneById($location_id);
            $this->playerLocationRepository->removeLocation($location, true);

            // Send Notication to Player
            $this->messageBus->dispatch(new PlayerMessage($location->getPlayer(), $location, PlayerAction::$DenyCheckin));
        }

        $stop_id = $user->getCardStop()->GetId();
        $pendingVerifications = $this->playerLocationRepository->findCardStopUnverified($stop_id, 50);

        return $this->render('card_stop/index.html.twig', [
            'controller_name' => 'CardStopDashboardController',
            'user' => $user,
            'pendingVerifications' => $pendingVerifications,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Poker Run Card Stop');
    }

    public function configureMenuItems(): iterable
    {
        // Get the user object
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $cardStopId = $user->getCardStop()->getId();

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        //yield MenuItem::linkToRoute('Profile', 'fa fa-building', 'app_cs_profile');
        yield MenuItem::linkTo(CardStopCrudController::class, 'Card Stop Edit', 'fa fa-building')
            ->setAction('edit')
            ->setEntityId($cardStopId);

        yield MenuItem::linkToRoute('Rules', 'fa fa-gavel', 'app_rules');
    }
}
