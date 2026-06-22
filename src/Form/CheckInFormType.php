<?php

namespace App\Form;

use App\Entity\CardStop;
use App\Entity\PlayerLocation;
use App\Repository\CardStopRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CheckInFormType extends AbstractType
{
    public function __construct(private Security $security) 
    {

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // grab the user, do a quick sanity check that one exists
        /*
        * @var \App\Entity\User $user
        */
        $user = $this->security->getUser();
        if (!$user) 
        {
            throw new \LogicException('The CheckInFormType cannot be used without an authenticated user!');
        }

        // Add the Player ID as a hidden field, since we need it to associate the check-in with the user, but we don't want the user to modify it
        $builder->add('player_id', HiddenType::class, ['property_path' => 'Player.id']);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
            if (null !== $event->getData()->getCardStop()) {
                // we don't need to add the field because
                // the message will be addressed to a fixed card stop
                return;
            }

            $form = $event->getForm();

            $formOptions = [
                'class' => CardStop::class,
                'choice_label' => 'card_stop_name',
                'query_builder' => function (CardStopRepository $repo) use ($user) {
                    // call a method on your repository that returns the query builder
                    return $repo->findAllUnvisitedCardStopsForPlayerQB($user->getId());
                },
            ];

            // create the field, this is similar the $builder->add()
            // field name, field type, field options
            $form->add('CardStop', EntityType::class, $formOptions);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayerLocation::class,
        ]);
    }
}
