<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PlayerPositionValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\PlayerPosition */

        if ($value === null || $value === '') {
            return;
        }
        
        /**
         * Solution proposed by the Doctrine Cookbook for emulating an enum type:
         * https://www.doctrine-project.org/projects/doctrine-orm/en/2.10/cookbook/mysql-enums.html#solution-1-mapping-to-varchars
         */
        if (!in_array($value, array(
            $constraint::POSITION_PORTERO,
            $constraint::POSITION_DEFENSA,
            $constraint::POSITION_CENTROCAMPISTA,
            $constraint::POSITION_DELANTERO
            ))
        ) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
