<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\CardStop;
use App\Entity\PlayerLocation;
use App\Entity\PlayingCard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Doctrine\Persistence\ManagerRegistry;

class PlayerLocationCrudController extends AbstractCrudController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return PlayerLocation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $playerLocation = $this->getContext()?->getEntity()->getInstance();
        
        $firstCardChoices = [];
        if ($playerLocation instanceof PlayerLocation && $playerLocation->getFirstCard() !== null) {
            $firstCardChoices = [$playerLocation->getFirstCard()];
        }

        $extraCardChoices = [];
        if ($playerLocation instanceof PlayerLocation && $playerLocation->getExtraCard() !== null) {
            $extraCardChoices = [$playerLocation->getExtraCard()];
        }

        $playerChoices = [];
        if ($playerLocation instanceof PlayerLocation && $playerLocation->getPlayer() !== null) {
            $playerChoices = [$playerLocation->getPlayer()];
        }

        $extraCardChoices = [];
        if ($playerLocation instanceof PlayerLocation && $playerLocation->getExtraCard() !== null) {
            $extraCardChoices = [$playerLocation->getExtraCard()];
        }

        $cardStopChoices = [];
        if ($playerLocation instanceof PlayerLocation && $playerLocation->getCardStop() !== null) {
            $cardStopChoices = [$playerLocation->getCardStop()];
        }

        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('isVerfied');
        yield AssociationField::new('first_card')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($first_card)
                {
                    return $first_card->getCardNumber() . ' of ' . $first_card->getCardSuit();
                },
                'choices' => $firstCardChoices,
            ]);
        yield AssociationField::new('extra_card')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($extra_card)
                {
                    return $extra_card->getCardNumber() . ' of ' . $extra_card->getCardSuit();
                },
                'choices' => $extraCardChoices,
            ]);
        yield TextField::new('checkinTime')
            ->onlyOnIndex();;
        yield DateField::new('verified_on')
            ->onlyOnIndex();;
        yield AssociationField::new('verified_by')
            ->onlyOnIndex();;
        yield AssociationField::new('Player')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($user)
                {
                    return $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getEmail() . ')';
                },
                'choices' => $playerChoices,
            ]);
        yield AssociationField::new('CardStop')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($cardStop)
                {
                    return $cardStop->getCardStopName();
                },
                'choices' => $cardStopChoices,
            ]);
    }
}
