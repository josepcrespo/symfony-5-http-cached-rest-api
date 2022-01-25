<?php

namespace App\Entity;

use App\Entity\BaseEntity;
use App\Entity\Team;
use App\Repository\PlayerRepository;
use App\Validator as AppAssert;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type as JMSType;
use Symfony\Component\Validator\Constraints as Assert;
 
/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 * @AppAssert\PlayerTeam
 */
class Player extends BaseEntity
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @JMSType("DateTimeInterface<'Y-m-d'>")
	 * @Orm\Column(type="datetime")
     */
    private $birth_date;

    /**
     * @ORM\Column(type="string", length=255)
     * @AppAssert\PlayerPosition
     */
    private $position;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $salary;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="players", fetch="EAGER")
     */
    private $team;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBirthDate(): ?DateTimeInterface
    {
        return $this->birth_date;
    }

    public function setBirthDate(DateTimeInterface  $birth_date): self
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getSalary(): ?int
    {
        return $this->salary;
    }

    public function setSalary(?int $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
