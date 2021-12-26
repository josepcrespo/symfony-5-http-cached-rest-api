<?php

namespace App\Entity;

use \DateTime;
use Doctrine\ORM\Mapping as Orm;
use Symfony\Component\Validator\Constraints as Assert;
// use JMS\Serializer\Annotation\Type;

class BaseEntity {
	/**
	 * @var int
	 *
	 * @Orm\Id
	 * @Orm\GeneratedValue
	 * @Orm\Column(type="integer")
	 */
	protected $id;

	/**
	 * @var DateTime
	 * @Orm\Column(type="datetime")
	 */
	protected $timestamp;

	public function __construct() {
		$this->setTimestamp(new \DateTime());
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getTimestamp(): ?\DateTimeInterface {
		return $this->timestamp;
	}

	public function setTimestamp(\DateTimeInterface $timestamp): self {
		$this->timestamp = $timestamp;
		
		return $this;
	}
}
