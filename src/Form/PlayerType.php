<?php

namespace App\Form;

use App\Entity\Player;
use App\Entity\Team;
use App\Exception\ResourceNotFoundException;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerType extends AbstractType
{
  /**
   * @var string
   */
  private const FORM_NAME = '';

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = $options['entity_manager'];

    $builder
      ->add('id', IntegerType::class, ['required' => true])
      ->add('name', TextType::class, ['required' => true])
      ->add('birth_date', DateType::class, [
               'input' => 'datetime',
        'input_format' => 'Y-m-d',
            'required' => true,
              'widget' => 'single_text'
      ])
      ->add('position', TextType::class, ['required' => true])
      ->add('salary', IntegerType::class, ['required' => false])
      ->add('email', EmailType::class, ['required' => true])
      ->add('team', EntityType::class, [
                 'class' => Team::class,
        'error_bubbling' => true,
              'required' => false
      ]);

    $builder->addEventListener(
      FormEvents::PRE_SUBMIT,
      function (FormEvent $event) use ($entityManager) {
        // Get the form data, that got submitted by the user.
        $data = $event->getData();
        $form = $event->getForm();

        // Get the form element configuration.
        // $teamConfig = $form->get('team')->getConfig();

        // Get the form element options.
        // $options = $teamConfig->getOptions();

        $teamPropName = 'team';

        if (array_key_exists($teamPropName, $data)) {
          $teamId = is_int($data[$teamPropName])
            ? $data[$teamPropName]
            : $data[$teamPropName]->getId();
          // We always want to get a real Team object.
          // Detaching the object from the Doctrine Entity Manager, forces
          // to fetch it from the database when using the `find` method.
          if (is_object($data[$teamPropName])) {
            $entityManager->detach($data[$teamPropName]);
          }
          $team = $entityManager->find(Team::class, $teamId);
          if (!$team) {
            throw new ResourceNotFoundException($teamPropName, $teamId);
          }
          $form->add(
            $teamPropName,
            EntityType::class, [
              'choice_label'=>'name',
                    'class' => Team::class,
                     'data' => $team,
                 'required' => false
            ]
          );
        } else {
          /**
           * https://fosrestbundle.readthedocs.io/en/3.x/2-the-view-layer.html#data-transformation
           * https://github.com/sonata-project/SonataAdminBundle/issues/3575#issuecomment-180790948
           * https://symfony.com/doc/5.3/reference/forms/types/entity.html#choice-label
           */
          $form->add($teamPropName, EntityType::class, [
            'choice_label'=>'name',
                  'class' => Team::class,
               'required' => false
          ]);
        }
      }
    );

  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setRequired('entity_manager');

    $resolver->setDefaults([
      'data_class' => Player::class,
    ]);
  }

  public function getBlockPrefix(): string
  {
    return self::FORM_NAME;
  }

  public function getName(): string
  {
    return self::FORM_NAME;
  }
}
