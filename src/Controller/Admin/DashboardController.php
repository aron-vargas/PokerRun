<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\CardStop;
use App\Entity\PlayerLocation;
use App\Entity\PokerHand;
use App\Message\PlayerMessage;
use App\DataFixtures\PlayerAction;
use App\Repository\PlayerLocationRepository;
use App\Repository\PlayingCardRepository;
use App\Repository\PokerHandRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Asset\Packages;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController {
    private PlayerLocationRepository $playerLocationRepository;
    private PlayingCardRepository $playingCardRepository;
    private PokerHandRepository $pokerHandRepository;
    private UserRepository $userRepository;
    private AdminUrlGenerator $adminUrlGenerator;
    private MessageBusInterface $messageBus;
    private Packages $assetManager;

    public function __construct(
        PlayerLocationRepository $playerLocationRepository,
        PlayingCardRepository $playingCardRepository,
        PokerHandRepository $pokerHandRepository,
        UserRepository $userRepository,
        AdminUrlGenerator $adminUrlGenerator,
        MessageBusInterface $messageBus,
        Packages $assetManager
    ) {
        $this->playerLocationRepository = $playerLocationRepository;
        $this->playingCardRepository = $playingCardRepository;
        $this->pokerHandRepository = $pokerHandRepository;
        $this->userRepository = $userRepository;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->messageBus = $messageBus;
        $this->assetManager = $assetManager;
    }

    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $totalPlayers = $this->userRepository->countAllUsers();
        $totalCheckIns = $this->playerLocationRepository->countCheckIns();
        $totalPokerCards = $this->playingCardRepository->countAllCards();
        $recentActivities = $this->playerLocationRepository->findRecentActivity(10);
        $currentPokerHands = $this->pokerHandRepository->findCurrentHighestHands(10);

        return $this->render('admin/index.html.twig', [
            'totalPlayers' => $totalPlayers,
            'totalCheckIns' => $totalCheckIns,
            'totalPokerCards' => $totalPokerCards,
            'recentActivities' => $recentActivities,
            'currentPokerHands' => $currentPokerHands,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/checkin/confirm/{location_id}/{player_id}', name: 'admin_checkin_confirm')]
    #[AdminRoute('/checkin/confirm/{location_id}/{player_id}', name: 'admin_checkin_confirm')]
    public function confirmCheckin(int $location_id, int $player_id): Response
    {
        // Get the user object
        /** @var User $user */
        $user = $this->getUser();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $location = $this->playerLocationRepository->findOneById($location_id);
        $this->playerLocationRepository->verifyLocation($location, $user->getId(), true);

        // Send Notication to Player
        $this->messageBus->dispatch(new PlayerMessage($location->getPlayer(), $location, PlayerAction::$ApproveCheckin));

        $url = $this->adminUrlGenerator
            ->setController(PlayerLocationCrudController::class) // Replace with your target CRUD controller
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/checkin/deny/{location_id}/{player_id}', name: 'admin_checkin_deny')]
    #[AdminRoute('/checkin/deny/{location_id}/{player_id}', name: 'admin_checkin_deny')]
    public function denyCheckin(int $location_id, int $player_id): Response
    {
        // Get the user object
        /** @var User $user */
        $user = $this->getUser();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $location = $this->playerLocationRepository->findOneById($location_id);
        $this->playerLocationRepository->removeLocation($location, true);

        // Send Notication to Player
        $this->messageBus->dispatch(new PlayerMessage($location->getPlayer(), $location, PlayerAction::$DenyCheckin));

        $url = $this->adminUrlGenerator
            ->setController(PlayerLocationCrudController::class) // Replace with your target CRUD controller
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        $src = $this->assetManager->getUrl('images/Main Street Fernley.png');
        $title = sprintf('<img src="%s" alt="Main Street Fernley" style="height: 35px;"><br/>Main Street Fernley - PokerRun', $src);
        return Dashboard::new()->setTitle($title);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-dashboard');
        yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fa fa-users')->setAction('index');
        // yield MenuItem::linkTo(RoleCrudController::class,'Roles', 'fa fa-circle-user')->setAction('index');
        yield MenuItem::linkTo(CardStopCrudController::class, 'Card Stops', 'fa fa-building')->setAction('index');
        yield MenuItem::linkTo(PlayerLocationCrudController::class, 'Player Stops', 'fa fa-map-marker')->setAction('index');
        yield MenuItem::linkTo(PokerHandCrudController::class, 'Poker Hands', 'fa fa-layer-group')->setAction('index');
        // yield MenuItem::linkTo(SomeCrudController::class, 'The Label', 'fas fa-list');

        yield MenuItem::linkToExitImpersonation('Exit Impersonation', 'fa fa-sign-out-alt');

        yield MenuItem::linkToRoute('Rules', 'fa fa-gavel', 'app_rules');
    }

    public function configureActions(): Actions
    {

        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets();
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        if (!$user instanceof User)
        {
            throw new \Exception('Invalid user');
        }

        return parent::configureUserMenu($user)
            ->setAvatarUrl($user->getAvatar())
            ->addMenuItems([
                MenuItem::linkToUrl('My Profile', 'fas fa-user', $this->generateUrl('app_profile_show'))
            ]);
    }
}
