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
use Symfony\Component\Asset\Packages;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;

class PlayerLocationCrudController extends AbstractCrudController {
    private ManagerRegistry $doctrine;
    private Packages $assetManager;

    public function __construct(ManagerRegistry $doctrine, Packages $assetManager)
    {
        $this->doctrine = $doctrine;
        $this->assetManager = $assetManager;
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

        $genericImageSrc = $this->assetManager->getUrl('images/Fernley.png');
        $assetManager = $this->assetManager;

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
            ])
            ->renderAsHtml()
            ->formatValue(static function ($value, PlayerLocation $location) use ($assetManager, $genericImageSrc)
            {
                $cardStop = $location->getCardStop();
                $logoSrc = trim((string) $cardStop->getLogo()) !== '' ? $assetManager->getUrl($cardStop->getLogo()) : $genericImageSrc;
                $safeLogoSrc = htmlspecialchars($logoSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeName = htmlspecialchars((string) $cardStop->getCardStopName(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                return sprintf(
                    '<span class="d-inline-flex align-items-center gap-2"><img src="%s" alt="%s" style="height:32px;width:32px;object-fit:contain;" />%s</span>',
                    $safeLogoSrc,
                    $safeName,
                    $safeName
                );
            });
    }
}
