<?php

namespace App\Controller\Admin;

use App\Entity\CardStop;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use Symfony\Component\Asset\Packages;

class CardStopCrudController extends AbstractCrudController {
    private ManagerRegistry $doctrine;
    private Packages $assetManager;

    public function __construct(ManagerRegistry $doctrine, Packages $assetManager)
    {
        $this->doctrine = $doctrine;
        $this->assetManager = $assetManager;
    }

    public static function getEntityFqcn(): string
    {
        return CardStop::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        $genericImageSrc = $this->assetManager->getUrl('images/Fernley.png');
        $assetManager = $this->assetManager;

        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('card_stop_name')
            ->onlyOnIndex()
            ->renderAsHtml()
            ->formatValue(static function ($value, CardStop $cardStop) use ($assetManager, $genericImageSrc)
            {
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
        yield TextField::new('card_stop_name')
            ->onlyOnForms();
        yield NumberField::new('longitude');
        yield NumberField::new('latitude');
        yield TextField::new('logo');
        yield AssociationField::new('admins')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'choice_label' => function ($user)
                {
                    return $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getEmail() . ')';
                },
                'choices' => $this->doctrine->getRepository(User::class)->findAll(),
            ]);
    }
}
