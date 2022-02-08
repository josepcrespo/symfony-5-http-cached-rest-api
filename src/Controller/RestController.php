<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Team;
use App\Exception\ResourceNotFoundException;
use App\Form\PlayerType;
use App\Form\TeamType;
use App\Serializer\Normalizer\TeamNormalizer;

use App\Service\FileUploader;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as RestAnnotation;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


/**
 * The rest api routes.
 */
class RestController extends AbstractFOSRestController
{
  /**
   * @var EntityManagerInterface
   */
  private $entityManager;

  /**
   * @var FileUploader
   */
  private $fileUploader;

  /**
   * @var UrlHelper
   */
  private $urlHelper;

  public function __construct(
    EntityManagerInterface $entityManager,
    FileUploader $fileUploader,
    ObjectNormalizer $normalizer,
    NotifierInterface $notifier,
    UrlHelper $urlHelper
  ) {
    $this->entityManager = $entityManager;
    $this->fileUploader = $fileUploader;
    $this->normalizer = $normalizer;
    $this->notifier = $notifier;
    $this->urlHelper = $urlHelper;

  }

  /**
   * Returns a resource with a matching id.
   *
   * @param string $resource
   * @param string $id
   * @return View
   * @throws ResourceNotFoundException
   * @RestAnnotation\Get("/{resource}/{id}")
   */
  public function getOne(string $resource, string $id): View {
    $repository = $this->entityManager->getRepository($resource);
    $entity = $repository->findOneBy(['id' => $id]);
    if (!$entity) {
      throw new ResourceNotFoundException($resource, $id);
    }
    return View::create($entity, Response::HTTP_OK);
  }

  /**
   * Returns the result of a query.
   *
   * @param string $resource The name of the resource.
   * @param Request $request
   * @return View
   * @RestAnnotation\Get("/{resource}")
   */
  public function getMany(string $resource, Request $request): View {
    $repository = $this->entityManager->getRepository($resource);
    if ($query = $request->query->all()) {
      $args = ['order' => null, 'limit' => null, 'offset' => null];
      $query = array_merge($args, $query);
      $args = array_intersect_key($query, $args);
      $query = array_diff_key($query, $args);
      $resource = $repository->findBy($query, ...$args);
    } else {
      $resource = $repository->findAll();
    }
    return View::create($resource, Response::HTTP_OK);
  }

  /**
   * Inserts a resource and returns the result.
   *
   * @param string $resource
   * @param Request $request
   * @return View
   * @RestAnnotation\Post("/{resource}")
   */
  public function post(
    string $resource,
    Request $request
  ): View {
    $entity = new $resource();

    $reflectedClass = new \ReflectionClass($entity);
    $formTypeClass = 'App\Form\\' . $reflectedClass->getShortName() . 'Type';
    $symfonyFormType = new $formTypeClass();
    $form = $this->createForm($symfonyFormType::class, $entity, [
      'entity_manager' => $this->entityManager
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entity = $this->prePersistEvents($entity, $request);

      $this->entityManager->persist($entity);
      $this->entityManager->flush();
      $entity = $this->postPersistEvents($entity);

      return View::create($entity, Response::HTTP_OK);
    } else {
      /**
       * Instead of returning the $form itself,
       * returning $form->getErrors(true) will return all errors in a flat array.
       *
       * Output sample:
       * {
       *   "form": {
       *     "code": 400,
       *     "message": "Validation Failed",
       *     "errors": {
       *       "children": {
       *         "id": {},
       *         "name": {},
       *         "birth_date": {},
       *         "position": {},
       *         "salary": {
       *           "errors": [
       *             "A player enrolled in a team must have a salary.",
       *             "The expense in salaries exceeds the maximum allowed for this team (4925047)."
       *           ]
       *         },
       *         "email": {},
       *         "team": {}
       *       }
       *     }
       *   },
       *   "errors": [
       *     "A player enrolled in a team must have a salary.",
       *     "The expense in salaries exceeds the maximum allowed for this team (4925047)."
       *   ]
       * }
       */
      return View::create($form->getErrors(true), Response::HTTP_BAD_REQUEST);
    }
  }

  /**
   * Replaces a resource with another, and returns the new one.
   * 
   * Using the `apfd` PHP extension is ESSENTIAL to let PHP's post handler parse
   * `multipart/form-data`, `application/x-www-form-urlencoded` or any other custom
   * registered form data handler, without regard to the request's request method.
   * @see https://pecl.php.net/package-info.php?package=apfd
   * @see https://stackoverflow.com/a/62576815/2332731
   * 
   * To install it using a Dockerfile type in it:
   * RUN install-php-extensions apfd
   *
   * @param string  $resource
   * @param string  $id
   * @param Request $request
   * @return View
   * @RestAnnotation\Patch(path="/{resource}/{id}")
   */
  public function patch(
    string $resource,
    string $id,
    Request $request
  ): View {
    $repository = $this->entityManager->getRepository($resource);
    $entity = $repository->find($id);
    if (!$entity) {
      throw new ResourceNotFoundException($resource, $id);
    }
    $reflectedClass = new \ReflectionClass($entity);
    $formTypeClass = 'App\Form\\' . $reflectedClass->getShortName() . 'Type';
    $symfonyFormType = new $formTypeClass();
    // https://medium.com/sroze/symfony2-form-patch-requests-and-handlerequest-form-is-never-valid-fed09c44f798
    $form = $this->createForm(
      $symfonyFormType::class,
      $entity, [
        'allow_file_upload' => true,
        'attr' => ['enctype' => 'multipart/form-data'],
        'entity_manager' => $this->entityManager,
        'error_bubbling' => true,
        'method' => 'PATCH'
      ]
    );
    // Don't set fields to NULL when they are missing in the submitted data.
    // https://stackoverflow.com/a/25295370/2332731
    // $form->submit($request->request->all(), false);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entity = $this->prePersistEvents($entity, $request);
      $this->entityManager->persist($entity);
      $this->entityManager->flush();
      $entity = $this->postPersistEvents($entity);

      return View::create($entity, Response::HTTP_OK);
    } else {
      return View::create($form->getErrors(true), Response::HTTP_BAD_REQUEST);
    }
  }

  /**
   * Deletes and returns a resource.
   *
   * @param string $resource
   * @param string $id
   * @return View
   * @RestAnnotation\Delete("/{resource}/{id}")
   */
  public function delete(string $resource, string $id): View {
    $repository = $this->entityManager->getRepository($resource);
    $entity = $repository->findOneBy(['id' => $id]);
    if (!$entity) {
      throw new ResourceNotFoundException($resource, $id);
    }
    $this->entityManager->remove($entity);
    $this->entityManager->flush();
    return View::create($entity, Response::HTTP_OK);
  }

  private function prePersistEvents(
    object $entity,
    Request $request
  ): object {
    if ($entity instanceof Team) {
      $this->uploadImage($request, $entity, 'emblem');
    }

    return $entity;
  }

  private function postPersistEvents(object $entity): object {
    if ($entity instanceof Player) {
      $this->sendNewPlayerEmail($entity);
    }
    if ($entity instanceof Team) {
      $entity = $this->normalizeEmblem($entity);
    }

    return $entity;
  }

  private function sendNewPlayerEmail(Player $player): void {
    // Create a Notification that has to be sent
    // using the "email" channel
    $notification =
      (new Notification('Has sido registrado en LaLiga', ['email']))
        ->content(
          'Hola ' . $player->getName() . '! ' .
          'Has sido dado de alta en la base de datos de LaLiga.'
        );

    // The receiver of the Notification
    $recipient = new Recipient($player->getEmail());


    // Send the notification to the recipient

    $this->notifier->send($notification, $recipient);
  }

  private function uploadImage (
    Request $request,
    object $entity,
    string $imagePropertyName
  ): object {    
    $imageFile = $request->files->get($imagePropertyName);
    if ($imageFile) {
      $imageFileName = $this->fileUploader->upload($imageFile);
      $entityImageSetter = 'set' . ucfirst($imagePropertyName);
      $entity->$entityImageSetter($imageFileName);
    }

    return $entity;
  }

  private function normalizeEmblem (Team $team): Team {
    $teamNormalizer = new TeamNormalizer($this->normalizer, $this->urlHelper);
    // https://symfony.com/doc/5.3/components/serializer.html#selecting-specific-attributes
    $normalizedTeam = $teamNormalizer->normalize(
      $team,
      null,
      [AbstractNormalizer::ATTRIBUTES => ['emblem']]
    );
    $team->setEmblem($normalizedTeam['emblem']);

    return $team;
  }
}
