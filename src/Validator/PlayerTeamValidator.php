<?php

namespace App\Validator;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PlayerTeamValidator extends ConstraintValidator
{
  /**
   * @var EntityManagerInterface
   */
  protected $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }

  public function validate($player, Constraint $constraint)
  {
    /**
    * @var $constraint \App\Validator\PlayerTeam
    */

    // Validation
    if ($player->getTeam() !== null) {
      $teamRepository = $this->entityManager->getRepository(Team::class);

      // Check for the player salary.
      if (!$player->getSalary()) {
        $this->context
          ->buildViolation($constraint::NULL_SALARY)
          ->atPath('salary')
          ->addViolation();
      }

      // Check for the max number of players in the team.
      if (
        (!$player->getId() &&
        ($teamRepository->numOfPlayers($player->getTeam()->getId()) ===
        $constraint::MAX_PLAYERS_NUM)) ||
        ($player->getId() &&
        ($teamRepository->numOfPlayers($player->getTeam()->getId()) >
        $constraint::MAX_PLAYERS_NUM))
      ) {
        $this->context
          ->buildViolation($constraint::MAX_PLAYERS)
          ->atPath('team')
          ->addViolation();
      }

      // Check for the salary limit of the team.
      if (
        $player->getTeam() &&
        $teamRepository->find($player->getTeam()->getId())
      ) {
        $teamSalaryLimit =
          $teamRepository->find($player->getTeam()->getId())->getSalaryLimit();
        if (
          ($teamRepository->salaryExpense($player->getTeam()->getId()) + $player->getSalary())
          > $teamRepository->find($player->getTeam()->getId())->getSalaryLimit()
        ) {
          $this->context
            ->buildViolation($constraint::MAX_SALARY_EXPENSE)
            ->setParameter('{{ teamSalaryLimit }}', $teamSalaryLimit)
            ->atPath('salary')
            ->addViolation();
        }
      }
    }
  }
}
