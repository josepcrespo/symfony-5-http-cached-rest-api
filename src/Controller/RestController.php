<?php

namespace App\Controller;

use App\Exception\ResourceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Laminas\Hydrator\ReflectionHydrator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * The rest api routes.
 */
class RestController extends AbstractFOSRestController {
	/**
	 * @var EntityManagerInterface
	 */
	private $manager;

	public function __construct(EntityManagerInterface $manager) {
		$this->manager = $manager;
	}

	/**
	 * Returns a resource with a matching id.
	 *
	 * @param string $resource
	 * @param string $document
	 * @return View
	 * @throws ResourceNotFoundException
	 * @Rest\Get("/{resource}/{id}")
	 */
	public function getOne(string $resource, string $id): View {
		$repository = $this->manager->getRepository($resource);
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
	 * @Rest\Get("/{resource}")
	 */
	public function getMany(string $resource, Request $request): View {
		$repository = $this->manager->getRepository($resource);
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
	 * @Rest\Post("/{resource}")
	 */
	public function post(
		string $resource,
		Request $request,
		ValidatorInterface $validator
	): View {
		$args = $request->request->all();
		if (!$args) {
			$args = (array) json_decode($request->getContent());
		}
		$entity = new $resource();
		$hydrator = new ReflectionHydrator();
		$hydrator->hydrate($args, $entity);
		
		// Validation
		$errors = $validator->validate($entity);
		if (count($errors) > 0) {
        /*
         * Uses a __toString method on the $errors variable which is a
         * ConstraintViolationList object. This gives us a nice string
         * for debugging.
         */
        // $errorsString = (string) $errors;

        return View::create($errors, Response::HTTP_BAD_REQUEST);
    }

		$this->manager->persist($entity);
		$this->manager->flush();
		return View::create($entity, Response::HTTP_OK);
	}

	/**
	 * Replaces a resource with another, and returns the new one.
	 *
	 * @param string  $resource
	 * @param string  $id
	 * @param Request $request
	 * @return View
	 * @Rest\Put("/{resource}/{id}")
	 */
	public function put(
		string $resource,
		string $id,
		Request $request,
		ValidatorInterface $validator
	): View {
		$repository = $this->manager->getRepository($resource);
		$entity = $repository->findOneBy(['id' => $id]);
		if (!$entity) {
			throw new ResourceNotFoundException($resource, $id);
		}
		$args = $request->request->all();
		if (!$args) {
			$args = (array) json_decode($request->getContent());
		}
		$hydrator = new ReflectionHydrator();
		$hydrator->hydrate($args, $entity);
		
		// Validation
		$errors = $validator->validate($entity);
		if (count($errors) > 0) {
        /*
         * Uses a __toString method on the $errors variable which is a
         * ConstraintViolationList object. This gives us a nice string
         * for debugging.
         */
        $errorsString = (string) $errors;

        return View::create($errors, new Response($errorsString));
    }

		$this->manager->persist($entity);
		$this->manager->flush();
		return View::create($entity, Response::HTTP_OK);
	}

	/**
	 * Deletes and returns a resource.
	 *
	 * @param string $resource
	 * @param string $id
	 * @return View
	 * @Rest\Delete("/{resource}/{id}")
	 */
	public function delete(string $resource, string $id): View {
		$repository = $this->manager->getRepository($resource);
		$entity = $repository->findOneBy(['id' => $id]);
		if (!$entity) {
			throw new ResourceNotFoundException($resource, $id);
		}
		$this->manager->remove($entity);
		$this->manager->flush();
		return View::create($entity, Response::HTTP_OK);
	}
}
