<?php

namespace App\Controller\Player;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/player', routeName: 'player')]
class PlayerDashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        // Get the user object
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->render('home/index.html.twig', [ 'user' => $user]);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('styles/poker.css');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Main Street Fernley - PokerRun');
    }

    public function configureMenuItems(): iterable
    {
        // Get the user object
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        if ($user->GetLocation() && $user->GetLocation()->isVerified())
        {
            if (!$user->getLocation()->getFirstCard())
            {
                yield MenuItem::linkToRoute('Pick Card', 'fa fa-card', 'app_pick_card');
            }
            
            yield MenuItem::linkToRoute('Check Out', 'fa fa-card', 'app_check_out');
        }
        else
        {
            yield MenuItem::linkToRoute('Check In', 'fa fa-chart-bar', 'app_check_in');
        }

        yield MenuItem::linkToRoute('Rules', 'fa fa-gavel', 'app_rules');
        // yield MenuItem::linkTo(SomeCrudController::class, 'The Label', 'fas fa-list');
    }
}
