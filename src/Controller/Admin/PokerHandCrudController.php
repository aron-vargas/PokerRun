<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\PlayingCard;
use App\Entity\PokerHand;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PokerHandCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PokerHand::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $hand = $this->getContext()?->getEntity()->getInstance();

        $oneChoices = [];
        if ($hand instanceof PokerHand && $hand->getCardOne() !== null) {
            $oneChoices = [$hand->getCardOne()];
        }

        $twoChoices = [];
        if ($hand instanceof PokerHand && $hand->getCardTwo() !== null) {
            $twoChoices = [$hand->getCardTwo()];
        }

        $threeChoices = [];
        if ($hand instanceof PokerHand && $hand->getCardThree() !== null) {
            $threeChoices = [$hand->getCardThree()];
        }

        $fourChoices = [];
        if ($hand instanceof PokerHand && $hand->getCardFour() !== null) {
            $fourChoices = [$hand->getCardFour()];
        }

        $fiveChoices = [];
        if ($hand instanceof PokerHand && $hand->getCardFive() !== null) {
            $fiveChoices = [$hand->getCardFive()];
        }

        $playerChoices = [];
        if ($hand instanceof PokerHand && $hand->getPlayer() !== null) {
            $playerChoices = [$hand->getPlayer()];
        }

        return [
            IdField::new('id'),
            AssociationField::new('Player')->setFormTypeOptions([
                'class' => User::class,
                'choice_label' => function ($user)
                {
                    return $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getEmail() . ')';
                },
                'choices' => $playerChoices,
            ]),
            AssociationField::new('cardOne')->setFormTypeOptions([
                'class' => PlayingCard::class,
                'choice_label' => function ($card)
                {
                    return $card->getCardNumber()->name . ' of ' . $card->getCardSuit()->name;
                },
                'choices' => $oneChoices,
            ]),
            AssociationField::new('cardTwo')->setFormTypeOptions([
                'class' => PlayingCard::class,
                'choice_label' => function ($card)
                {
                    return $card->getCardNumber()->name . ' of ' . $card->getCardSuit()->name;
                },
                'choices' => $twoChoices,
            ]),
            AssociationField::new('cardThree')->setFormTypeOptions([
                'class' => PlayingCard::class,
                'choice_label' => function ($card)
                {
                    return $card->getCardNumber()->name . ' of ' . $card->getCardSuit()->name;
                },
                'choices' => $threeChoices,
            ]),
            AssociationField::new('cardFour')->setFormTypeOptions([
                'class' => PlayingCard::class,
                'choice_label' => function ($card)
                {
                    return $card->getCardNumber()->name . ' of ' . $card->getCardSuit()->name;
                },
                'choices' => $fourChoices,
            ]),
            AssociationField::new('cardFive')->setFormTypeOptions([
                'class' => PlayingCard::class,
                'choice_label' => function ($card)
                {
                    return $card->getCardNumber()->name . ' of ' . $card->getCardSuit()->name;
                },
                'choices' => $fiveChoices,
            ])
        ];
    }
}
