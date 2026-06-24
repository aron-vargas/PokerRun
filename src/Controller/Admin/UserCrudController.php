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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;


    public function __construct(UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine)
    {
        $this->passwordHasher = $passwordHasher;
        $this->doctrine = $doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        // Create the custom Impersonate action
        $impersonateAction = Action::new('impersonateUser', 'Impersonate', 'fa fa-user-secret')
            ->linkToUrl(function (User $user) {
                // Adjust 'email' to whatever identifier your user provider uses (e.g., getUsername)
                return $this->generateUrl('admin', [
                    '_switch_user' => $user->getEmail(),
                ]);
            });
        $actions->add(Crud::PAGE_INDEX, $impersonateAction);

        return parent::configureActions($actions);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('firstName');
        yield TextField::new('lastName');
        yield TextField::new('email');
        yield TextField::new('plainPassword', 'Password')
            ->onlyOnForms()
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'New Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'required' => false, // Makes it optional!
            ]);
        yield BooleanField::new('active');
        yield BooleanField::new('isVerified');
        yield DateField::new('createdOn')
            ->onlyOnIndex();
        yield IntegerField::new('createdBy')
            ->onlyOnIndex();
        yield DateField::new('modifiedOn')
            ->onlyOnIndex();
        yield IntegerField::new('modifiedBy')
            ->onlyOnIndex();

        //yield AvatarField::new('avatar')
        //    ->formatValue(static function ($value, User $user) {
        //        return $user->getAvatar();
        //    })
        //    ->hideOnForm();
        // yield ImageField::new('avatar')
        //     ->setBasePath('uploads/avatars')
        //     ->setUploadDir('public/avatars/uploads')
        //     ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
        //     ->onlyOnForms();
        /*
        yield AssociationField::new('roles')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'choice_label' => 'id',
                'choices' => $this->doctrine->getRepository(User::class)->findAll(),
            ]);
        */
            
        yield ChoiceField::new('roles')
            ->setChoices([
                'Super Admin' => 'ROLE_SUPER_ADMIN',
                'Admin' => 'ROLE_ADMIN',
                'User' => 'ROLE_USER',
                'Player' => 'ROLE_PLAYER',
                'Card Stop' => 'ROLE_CARD_STOP',
                'Impersonator' => 'ROLE_ALLOWED_TO_SWITCH',
            ])
            ->allowMultipleChoices()
            ->renderAsBadges();
        /*
        yield ChoiceField::new('roles')
            ->setFormType(ChoiceType::class) // Forces standard Symfony ChoiceType behavior
            ->setFormTypeOptions([
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'choices'  => [
                    'Super Admin' => 'ROLE_SUPER_ADMIN',
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER',
                    'Player' => 'ROLE_PLAYER',
                    'Card Stop' => 'ROLE_CARD_STOP',
                    'Impersonator' => 'ROLE_ALLOWED_TO_SWITCH',
                ],
            ]);
            */
        yield AssociationField::new('cardStop')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => 'id',
                'required' => false,
                'choices' => $this->doctrine->getRepository(CardStop::class)->findAll(),
            ]);
        yield AssociationField::new('pokerHand')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => false,
                'choice_label' => 'id',
                'required' => false,
                'choices' => $this->doctrine->getRepository(PokerHand::class)->findAll(),
            ]);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encodePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encodePassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    private function encodePassword(User $user): void
    {
        if ($user->getPlainPassword()) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $user->getPlainPassword())
            );
            // Important: erase credentials to clear plaintext from memory
            $user->setPlainPassword(null); 
        }
    }
}
