<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PlayerTeamId extends Constraint
{
    const NULL_SALARY = 'A player enrolled in a team must have a salary.';
    const MAX_PLAYERS = 'The max number (5) of players which can be enrolled in this team has been reached.';
    const MAX_PLAYERS_NUM = 5;
    const MAX_SALARY_EXPENSE = 'The expense in salaries exceeds the maximum allowed for this team ({{ teamSalaryLimit }}).';

    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'The player can not be enrolled into the { teamName }.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
 
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
