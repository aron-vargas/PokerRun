<?php

namespace App\Controller\Admin;

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
            AssociationField::new('player')->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($user)
                {
                    return $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getEmail() . ')';
                },
                'choices' => $playerChoices,
            ]),
            AssociationField::new('card_one')->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($card)
                {
                    return $card->getCardNumber() . ' of ' . $card->getCardSuit();
                },
                'choices' => $oneChoices,
            ]),
            AssociationField::new('card_two')->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($card)
                {
                    return $card->getCardNumber() . ' of ' . $card->getCardSuit();
                },
                'choices' => $twoChoices,
            ]),
            AssociationField::new('card_three')->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($card)
                {
                    return $card->getCardNumber() . ' of ' . $card->getCardSuit();
                },
                'choices' => $threeChoices,
            ]),
            AssociationField::new('card_four')->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($card)
                {
                    return $card->getCardNumber() . ' of ' . $card->getCardSuit();
                },
                'choices' => $fourChoices,
            ]),
            AssociationField::new('card_five')->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($card)
                {
                    return $card->getCardNumber() . ' of ' . $card->getCardSuit();
                },
                'choices' => $fiveChoices,
            ])
        ];
    }
}
