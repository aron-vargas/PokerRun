<?php

namespace App\Controller\CardStop;

use App\Repository\PlayerLocationRepository;
use App\Controller\Admin\CardStopCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/cardstop', routeName: 'cardstop')]
class CardStopDashboardController extends AbstractDashboardController
{
    public function __construct(private PlayerLocationRepository $playerLocationRepository)
    {

    }

    public function index(): Response
    {
        // Get the user object
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $stop_id = $user->getCardStop()->GetId();
        $pendingVerifications = $this->playerLocationRepository->findCardStopUnverified($stop_id, 50);

        return $this->render('card_stop/index.html.twig', [ 'user' => $user, 'pendingVerifications' => $pendingVerifications]);
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
        yield MenuItem::linkTo(CardStopCrudController::class,'Edit Stop', 'fa fa-building')
            ->setAction('edit')
            ->setEntityId($cardStopId);
    }
}
