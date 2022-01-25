<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
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
      ->add('emblem', TextType::class, ['required' => false])
      ->add('salary_limit', IntegerType::class, ['required' => true])
      ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Team::class,
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
