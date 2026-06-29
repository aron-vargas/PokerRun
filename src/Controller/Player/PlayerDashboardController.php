<?php

namespace App\Controller\Player;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        if (!$user instanceof User) {
            throw new \Exception('Invalid user');
        }

        return parent::configureUserMenu($user)
            ->setAvatarUrl($user->getAvatar())
            ->addMenuItems([
                MenuItem::linkToUrl('My Profile', 'fas fa-user', $this->generateUrl('app_profile_show'))
            ]);
    }

    public function configureMenuItems(): iterable
    {
        // Get the user object
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        if ($user->GetLocation() && $user->GetLocation()->isVerified())
        {
            yield MenuItem::linkToRoute('Pick Card', 'fa fa-card', 'app_pick_card');
            yield MenuItem::linkToRoute('Pick Card', 'fa fa-card', 'app_check_out');

            if (!$user->GetLocation()->getExtraCard())
                yield MenuItem::linkToRoute('Pick Card', 'fa fa-card', 'app_extra_card');
        }
        else
        {
            yield MenuItem::linkToRoute('Check In', 'fa fa-chart-bar', 'app_check_in');
        }
        // yield MenuItem::linkTo(SomeCrudController::class, 'The Label', 'fas fa-list');
    }
}
