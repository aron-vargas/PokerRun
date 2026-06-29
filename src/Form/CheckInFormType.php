<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\CardStop;
use App\Entity\PlayerLocation;
use App\Repository\CardStopRepository;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CheckInFormType extends AbstractType {
    public function __construct(private Security $security, private Packages $assetManager)
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
        $builder->add('player_id', HiddenType::class, ['property_path' => 'Player.id', 'mapped' => false]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user, $options)
        {
            if (null !== $event->getData()->getCardStop())
            {
                // we don't need to add the field because
                // the message will be addressed to a fixed card stop
                return;
            }

            $form = $event->getForm();

            $genericImageSrc = $this->assetManager->getUrl('images/Fernley.png');

            $formOptions = [
                'class' => CardStop::class,
                // render as radio buttons
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                // allow HTML inside the label (we build an <img> + name)
                'label_html' => true,
                'choice_label' => function (CardStop $cardStop) use ($genericImageSrc)
                {
                    $name = htmlspecialchars((string) $cardStop->getCardStopName(), ENT_QUOTES, 'UTF-8');
                    $logo = $cardStop->getLogo();

                    if ($logo)
                    {
                        $src = $logo;
                    }
                    else
                    {
                        // fallback generic building image from public assets
                        $src = $genericImageSrc;
                    }

                    return sprintf('<img src="%s" alt="%s logo" style="height:24px;width:24px;object-fit:cover;margin-right:8px;border-radius:4px;vertical-align:middle;">%s', $src, $name, $name);
                },
                'query_builder' => function (CardStopRepository $repo) use ($user, $options)
                {
                    // call a method on your repository that returns the query builder
                    return $repo->findAllUnvisitedCardStopsForPlayerQB($user->getId());
                },
            ];

            // create the field, this is similar the $builder->add()
            // field name, field type, field options
            $form->add('CardStop', EntityType::class, $formOptions);
            $form->add('save', SubmitType::class, [
                'label' => 'Check It', // Text displayed on the button
                'attr' => ['class' => 'btn btn-primary', 'disabled' => !$options['user_is_verified']] // Optional: add CSS classes
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayerLocation::class,
            'csrf_token_id' => 'check_in',
            'user_is_verified' => false,
        ]);
    }
}
