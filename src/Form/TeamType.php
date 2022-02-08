<?php

namespace App\Form;

use App\Entity\Team;
use App\Form\PlayerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class TeamType extends AbstractType
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
      ->add('emblem', FileType::class, [
        // Unmapped means that this field is not associated to any entity property.
        'mapped' => false,
        // Make it optional so you don't have to re-upload the file
        // every time you edit the Product details.
        'required' => false,
        // Unmapped fields can't define their validation using annotations
        // in the associated entity, so you can use the PHP constraint classes.
        'constraints' => [
          new Image([
            'maxSize' => '1024k',
            'mimeTypes' => [
              'image/gif',
              'image/jpeg',
              'image/png'
            ],
            'mimeTypesMessage' => 'Please upload a valid Image file (gif, jpeg, png).'
          ])
        ]
      ])
      ->add('salary_limit', IntegerType::class, ['required' => true])
      ->add('players', CollectionType::class, [
              'allow_add' => true,
           'allow_delete' => true,
           'by_reference' => false,
           'delete_empty' => true,
             'entry_type' => PlayerType::class,
          'entry_options' => [ 'entity_manager' => $entityManager ],
         'error_bubbling' => true,
        'invalid_message' => 'Not valid player.',
               'required' => false
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setRequired('entity_manager');
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
