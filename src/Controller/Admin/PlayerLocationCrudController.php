<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\CardStop;
use App\Entity\PlayerLocation;
use App\Entity\PlayingCard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;

class PlayerLocationCrudController extends AbstractCrudController {
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return PlayerLocation::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $confirmCheckinAction = Action::new('confirmCheckin', 'Confirm Check-In', 'fa fa-check')
            ->linkToRoute('admin_checkin_confirm', function (PlayerLocation $playerLocation): array
            {
                return [
                    'location_id' => $playerLocation->getId(),
                    'player_id' => $playerLocation->getPlayer()?->getId(),
                ];
            })
            ->displayIf(function (PlayerLocation $playerLocation): bool
            {
                return !$playerLocation->isVerified();
            });

        $actions->add(Crud::PAGE_INDEX, $confirmCheckinAction);

        return parent::configureActions($actions);
    }


    public function configureFields(string $pageName): iterable
    {
        $playerLocation = $this->getContext()?->getEntity()->getInstance();

        $firstCardChoices = [];
        if ($playerLocation instanceof PlayerLocation && $playerLocation->getFirstCard() !== null)
        {
            $firstCardChoices = [$playerLocation->getFirstCard()];
        }

        $extraCardChoices = [];
        if ($playerLocation instanceof PlayerLocation && $playerLocation->getExtraCard() !== null)
        {
            $extraCardChoices = [$playerLocation->getExtraCard()];
        }

        $playerChoices = [];
        if ($playerLocation instanceof PlayerLocation && $playerLocation->getPlayer() !== null)
        {
            $playerChoices = [$playerLocation->getPlayer()];
        }

        $extraCardChoices = [];
        if ($playerLocation instanceof PlayerLocation && $playerLocation->getExtraCard() !== null)
        {
            $extraCardChoices = [$playerLocation->getExtraCard()];
        }

        $cardStopChoices = [];
        if ($playerLocation instanceof PlayerLocation && $playerLocation->getCardStop() !== null)
        {
            $cardStopChoices = [$playerLocation->getCardStop()];
        }

        yield IdField::new('id')
            ->onlyOnIndex();
        yield BooleanField::new('isVerified');
        yield AssociationField::new('firstCard')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($first_card)
                {
                    return $first_card->getCardNumber() . ' of ' . $first_card->getCardSuit();
                },
                'choices' => $firstCardChoices,
            ])
            ->onlyOnIndex();
        // yield AssociationField::new('extra_card')
        //     ->setFormTypeOptions([
        //         'by_reference' => false,
        //         'multiple' => false,
        //         'choice_label' => function ($extra_card)
        //         {
        //             return $extra_card->getCardNumber() . ' of ' . $extra_card->getCardSuit();
        //         },
        //         'choices' => $extraCardChoices,
        //     ]);
        yield DateField::new('checkinTime');
        yield DateField::new('verified_on');
        yield AssociationField::new('verified_by')
            ->onlyOnIndex();
        yield AssociationField::new('Player')
            ->setFormTypeOptions([
                'class' => User::class,
                'choice_label' => function ($player)
                {
                    return $player->getFirstName() . ' ' . $player->getLastName() . ' (' . $player->getEmail() . ')';
                },
                'choices' => $playerChoices,
            ]);
        yield AssociationField::new('CardStop')
            ->setFormTypeOptions([
                'class' => CardStop::class,
                'choice_label' => 'card_stop_name',
                'choices' => $cardStopChoices,
            ]);
    }
}
