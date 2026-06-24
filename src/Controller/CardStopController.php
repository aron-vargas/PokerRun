<?php

namespace App\Controller;

use App\Entity\CardStop;
use App\Repository\PlayerLocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;

final class CardStopController extends AbstractController
{
    public function __construct(private PlayerLocationRepository $locationRepo)
    {

    }

    #[AdminRoute('/', name: 'app_card_stop')]
    public function index(): Response
    {
        return $this->render('card_stop/index.html.twig', [
            'controller_name' => 'CardStopController',
        ]);
    }

    #[AdminRoute('/checkin/confirm', name: 'checkin_confirm')]
    public function confirmCheckin(int $location_id, int $player_id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->isGranted('ROLE_CARD_STOP'))
        {
            $location = $this->locationRepo->GetOneById($location_id);
            $this->locationRepo->verifyLocation($location, true);
        }

        return $this->render('card_stop/index.html.twig', [
            'controller_name' => 'CardStopController',
        ]);
    }

    #[AdminRoute('/checkin/deny', name: 'checkin_deny')]
    public function denyCheckin(int $location_id, int $player_id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->isGranted('ROLE_CARD_STOP'))
        {
            $location = $this->locationRepo->GetOneById($location_id);
            $this->locationRepo->removeLocation($location, true);
        }

        return $this->render('card_stop/index.html.twig', [
            'controller_name' => 'CardStopController',
        ]);
    }

    // #[Route('/cardstop/edit', name: 'edit_card_stop')]
    // #[AdminRoute('/edit', name: 'edit_card_stop')]
    // public function edit(CardStop $cardStop, Request $request, EntityManagerInterface $em): Responce
    // {
    //     $form = $this->createForm(CardStopFormType::class, $cardStop);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         /** @var UploadedFile $uploadedFile */
    //         $uploadedFile = $form['imageFile']->getData();
    //         $destination = $this->getParameter('kernel.project_dir').'/public/uploads';
    //         $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
    //         $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
    //         $uploadedFile->move(
    //             $destination,
    //             $newFilename
    //         );
    //         $article->setImageFilename($newFilename);
    //     }

    //     return $this->render($twig = 'home/index.html.twig';, [
    //         'form' => $form->createView()
    //     ]);
    // }

    #[AdminRoute('/profile', name: 'app_cs_profile')]
    public function profile(): Response
    {
        return $this->render('card_stop/profile.html.twig', [
            'controller_name' => 'CardStopController',
        ]);
    }
}
