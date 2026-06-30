<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\CardStop;
use App\Entity\Role;
use App\Entity\PokerHand;
use App\Repository\CardStopRepository;
use Symfony\Component\Asset\Packages;
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
use Doctrine\ORM\QueryBuilder;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    //private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    //private CardStopRepository $cardStopRepository;


    public function __construct(UserPasswordHasherInterface $passwordHasher, private Packages $assetManager)
    {
        $this->passwordHasher = $passwordHasher;
        //$this->doctrine = $doctrine;
        $this->assetManager = $assetManager;
        //$this->cardStopRepository = $cardStopRepository;
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
        $genericImageSrc = $this->assetManager->getUrl('images/Fernley.png');

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

        // Admin-only fields
        if ($this->isGranted('ROLE_ADMIN'))
        {
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

            yield AssociationField::new('cardStop')
                ->setFormTypeOptions([
                    'class' => CardStop::class,
                    'choice_label' => 'card_stop_name',
                ]);

        }


       // return $qb;
        // yield AssociationField::new('pokerHand')
        //     ->setFormTypeOptions([
        //         'by_reference' => false,
        //         'multiple' => false,
        //         'choice_label' => 'id',
        //         'required' => false,
        //         'choices' => $this->doctrine->getRepository(PokerHand::class)->findAll(),
        //     ]);
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
