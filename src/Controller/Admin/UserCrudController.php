<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\CardStop;
use App\Entity\Role;
use App\Entity\PokerHand;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\Persistence\ManagerRegistry;

class UserCrudController extends AbstractCrudController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('firstName');
        yield TextField::new('lastName');
        yield TextField::new('email');
        yield BooleanField::new('active')
            ->onlyOnIndex();
        yield BooleanField::new('isVerified')
            ->onlyOnIndex();
        yield DateField::new('createdOn')
            ->onlyOnIndex();
        yield IntegerField::new('createdBy')
            ->onlyOnIndex();
        yield DateField::new('modifiedOn')
            ->onlyOnIndex();
        yield IntegerField::new('modifiedBy')
            ->onlyOnIndex();

        yield AvatarField::new('avatar')
            ->formatValue(static function ($value, User $user) {
                return $user->getAvatar();
            })
            ->hideOnForm();
        yield ImageField::new('avatar')
            ->setBasePath('uploads/avatars')
            ->setUploadDir('public/avatars/uploads')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->onlyOnForms();
        yield AssociationField::new('roles')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'choice_label' => function ($role) {
                    return $role;
                },
                'choices' => $this->doctrine->getRepository(User::class)->findAll(),
            ]);

        yield AssociationField::new('cardStop')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($cardStop) {
                    return $cardStop->getCardStopName();
                },
                'choices' => $this->doctrine->getRepository(CardStop::class)->findAll(),
            ]);
        yield AssociationField::new('pokerHand')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => function ($pokerHand) {
                    return $pokerHand->getId();
                },
                'choices' => $this->doctrine->getRepository(PokerHand::class)->findAll(),
            ]);
        //$roles = ['SUPER_ADMIN', 'ADMIN', 'CARD_STOP', 'PLAYER'];
        //yield ArrayField::new('roles')
        //    ->setFormType(ChoiceType::class)
        //    ->setFormTypeOptions([
        //        'choices' => array_combine($roles, $roles),
        //        'multiple' => true,
         //       'expanded' => true,
         //   ]);
    }
}
