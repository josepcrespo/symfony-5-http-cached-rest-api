<?php

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PlayerTeamIdValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function validate($player, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\PlayerTeamId */

        // Validation
        if ($player->getTeamId() !== null) {
            $teamRepository = $this->em->getRepository('App:Team');

            // Check for the player salary.
            if (!$player->getSalary()) {
                $this->context
                    ->buildViolation($constraint::NULL_SALARY)
                    ->atPath('salary')
                    ->addViolation();
            }

            // Check for the max number of players in the team.
            if ($teamRepository->numOfPlayers($player->getTeamId()) >= 5) {
                $this->context
                    ->buildViolation($constraint::MAX_PLAYERS)
                    ->atPath('team')
                    ->addViolation();
            }

            // Check for the salary limit of the team.
            $teamSalaryLimit =
                $teamRepository->find($player->getTeamId())->getSalaryLimit();
            if (
                ($teamRepository->salaryExpense($player->getTeamId()) + $player->getSalary())
                > $teamRepository->find($player->getTeamId())->getSalaryLimit()
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
