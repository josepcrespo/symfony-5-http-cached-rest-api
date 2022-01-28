<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Team;
use App\Exception\ResourceNotFoundException;
use App\Form\PlayerType;
use App\Form\TeamType;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as RestAnnotation;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

/**
 * The rest api routes.
 */
class RestController extends AbstractFOSRestController
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
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
		Request $request,
		NotifierInterface $notifier
	): View {
		$requestBody = $request->request->all()
			? $request->request->all()
			: (array) json_decode($request->getContent());
		$entity = new $resource($this->entityManager);

		$form = $this->createForm(PlayerType::class, $entity);
		$form->submit($requestBody);
			
		if ($form->isSubmitted() && $form->isValid()) {
			$this->entityManager->persist($entity);
			$this->entityManager->flush();
			if ($entity instanceof Player) {
				$this->sendNewPlayerEmail($entity, $notifier);
			}
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
	 * @param string  $resource
	 * @param string  $id
	 * @param Request $request
	 * @return View
	 * @RestAnnotation\Put(path="/{resource}/{id}")
	 */
	public function put(
		string $resource,
		string $id,
		Request $request
	): View {
		$requestBody = $request->request->all()
			? $request->request->all()
			: (array) json_decode($request->getContent());
		$repository = $this->entityManager->getRepository($resource);
		$entity = $repository->findOneBy(['id' => $id]);
		if (!$entity) {
			throw new ResourceNotFoundException($resource, $id);
		}
		$reflectedClass = new \ReflectionClass($entity);
		$formTypeClass = 'App\Form\\' . $reflectedClass->getShortName() . 'Type';
		$symfonyFormType = new $formTypeClass();
		$form = $this->createForm(
			$symfonyFormType::class,
			$entity,
			[ 'method' => 'PUT' ]
		);
		// Don't set fields to NULL when they are missing in the submitted data.
		// https://stackoverflow.com/a/25295370/2332731
		$form->submit($requestBody, false);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$this->entityManager->persist($entity);
			$this->entityManager->flush();
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

	private function sendNewPlayerEmail(
		Player $player,
		NotifierInterface $notifier
	): Void {
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
			$notifier->send($notification, $recipient);
	}
}
