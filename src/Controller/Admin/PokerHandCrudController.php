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
use Symfony\Component\DependencyInjection\Compiler\RemovePrivateAliasesPass;
use Symfony\Component\Asset\Packages;

class PokerHandCrudController extends AbstractCrudController
{
    public function __construct(private Packages $assetManager)
    {

    }

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

        $genericImageSrc = $this->assetManager->getUrl('images/PlayingCards/red-back.png');
        $assetManager = $this->assetManager;

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
                    return ucfirst($card->getCardNumber()->name) . ' of ' . $card->getCardSuit()->name;
                },
                'choices' => $oneChoices,
            ])
            ->renderAsHtml()
            ->formatValue(static function ($value, PokerHand $hand) use ($assetManager, $genericImageSrc)
            {
                $card = $hand->getCardOne();
                $logoSrc = $card && $card->getImage() ? $assetManager->getUrl($card->getImage()) : $genericImageSrc;
                $safeLogoSrc = htmlspecialchars($logoSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeName = htmlspecialchars($card ? ucfirst($card->getCardNumber()->name) . ' of ' . $card->getCardSuit()->name : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                return sprintf('<div class="text-center"><img src="%s" alt="%s" style="height:50px;width:object-fit:contain;" /></div><div class="text-center">%s</div>',
                    $safeLogoSrc,
                    $safeName,
                    $safeName
                );
            }),
            AssociationField::new('cardTwo')->setFormTypeOptions([
                'class' => PlayingCard::class,
                'choice_label' => function ($card)
                {
                    return ucfirst($card->getCardNumber()->name) . ' of ' . $card->getCardSuit()->name;
                },
                'choices' => $twoChoices,
            ])
            ->renderAsHtml()
            ->formatValue(static function ($value, PokerHand $hand) use ($assetManager, $genericImageSrc)
            {
                $card = $hand->getCardTwo();
                $logoSrc = $card && $card->getImage() ? $assetManager->getUrl($card->getImage()) : $genericImageSrc;
                $safeLogoSrc = htmlspecialchars($logoSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeName = htmlspecialchars($card ? ucfirst($card->getCardNumber()->name) . ' of ' . $card->getCardSuit()->name : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                return sprintf('<div class="text-center"><img src="%s" alt="%s" style="height:50px;width:object-fit:contain;" /></div><div class="text-center">%s</div>',
                    $safeLogoSrc,
                    $safeName,
                    $safeName
                );
            }),
            AssociationField::new('cardThree')->setFormTypeOptions([
                'class' => PlayingCard::class,
                'choice_label' => function ($card)
                {
                    return ucfirst($card->getCardNumber()->name) . ' of ' . $card->getCardSuit()->name;
                },
                'choices' => $threeChoices,
            ])
            ->renderAsHtml()
            ->formatValue(static function ($value, PokerHand $hand) use ($assetManager, $genericImageSrc)
            {
                $card = $hand->getCardThree();
                $logoSrc = $card && $card->getImage() ? $assetManager->getUrl($card->getImage()) : $genericImageSrc;
                $safeLogoSrc = htmlspecialchars($logoSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeName = htmlspecialchars($card ? ucfirst($card->getCardNumber()->name) . ' of ' . $card->getCardSuit()->name : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                return sprintf('<div class="text-center"><img src="%s" alt="%s" style="height:50px;width:object-fit:contain;" /></div><div class="text-center">%s</div>',
                    $safeLogoSrc,
                    $safeName,
                    $safeName
                );
            }),
            AssociationField::new('cardFour')->setFormTypeOptions([
                'class' => PlayingCard::class,
                'choice_label' => function ($card)
                {
                    return ucfirst($card->getCardNumber()->name) . ' of ' . $card->getCardSuit()->name;
                },
                'choices' => $fourChoices,
            ])
            ->renderAsHtml()
            ->formatValue(static function ($value, PokerHand $hand) use ($assetManager, $genericImageSrc)
            {
                $card = $hand->getCardFour();
                $logoSrc = $card && $card->getImage() ? $assetManager->getUrl($card->getImage()) : $genericImageSrc;
                $safeLogoSrc = htmlspecialchars($logoSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeName = htmlspecialchars($card ? ucfirst($card->getCardNumber()->name) . ' of ' . $card->getCardSuit()->name : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                return sprintf('<div class="text-center"><img src="%s" alt="%s" style="height:50px;width:object-fit:contain;" /></div><div class="text-center">%s</div>',
                    $safeLogoSrc,
                    $safeName,
                    $safeName
                );
            }),
            AssociationField::new('cardFive')->setFormTypeOptions([
                'class' => PlayingCard::class,
                'choice_label' => function ($card)
                {
                    return ucfirst($card->getCardNumber()->name) . ' of ' . $card->getCardSuit()->name;
                },
                'choices' => $fiveChoices,
            ])
            ->renderAsHtml()
            ->formatValue(static function ($value, PokerHand $hand) use ($assetManager, $genericImageSrc)
            {
                $card = $hand->getCardFive();
                $logoSrc = $card && $card->getImage() ? $assetManager->getUrl($card->getImage()) : $genericImageSrc;
                $safeLogoSrc = htmlspecialchars($logoSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeName = htmlspecialchars($card ? ucfirst($card->getCardNumber()->name) . ' of ' . $card->getCardSuit()->name : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                return sprintf('<div class="text-center"><img src="%s" alt="%s" style="height:50px;width:object-fit:contain;" /></div><div class="text-center">%s</div>',
                    $safeLogoSrc,
                    $safeName,
                    $safeName
                );
            }),
        ];
    }
}
