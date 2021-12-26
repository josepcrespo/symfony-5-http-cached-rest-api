<?php

namespace App\Entity;

use App\Validator as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PlayerRepository;
use Symfony\Component\Validator\Constraints as Assert;
// use JMS\Serializer\Annotation\Type;
 
/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 * @AppAssert\PlayerTeamId
 */
class Player extends BaseEntity
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string")
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $team_id;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="players", fetch="EAGER")
     */
    private $team;

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

    public function getBirthDate(): ?string
    {
        return $this->birth_date;
    }

    public function setBirthDate(string $birth_date): self
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

    public function getTeamId(): ?int
    {
        return $this->team_id;
    }

    public function setTeamId(?int $team_id): self
    {
        // $teamRepository = 
        //     $this->getDoctrine()->getManager()->getRepository('App:Team');

        $this->team_id = $team_id;
        // $this->setTeam($teamRepository->find($team_id));

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
}
