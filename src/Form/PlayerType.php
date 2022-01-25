<?php

namespace App\Form;

use App\Entity\Player;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PlayerType extends AbstractType
{
  /**
   * @var string
   */
  private const FORM_NAME = '';

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
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
      // ->add('team_id', IntegerType::class, ['required' => false])
      // https://fosrestbundle.readthedocs.io/en/3.x/2-the-view-layer.html#data-transformation
      // https://github.com/sonata-project/SonataAdminBundle/issues/3575#issuecomment-180790948
      // https://symfony.com/doc/5.3/reference/forms/types/entity.html#choice-label
      ->add('team', EntityType::class, [
               'class' => Team::class,
        'choice_label' => 'id',
            'required' => false
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
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
