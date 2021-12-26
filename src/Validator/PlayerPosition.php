<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PlayerPosition extends Constraint
{
    const POSITION_PORTERO        = 'Portero';
    const POSITION_DEFENSA        = 'Defensa';
    const POSITION_CENTROCAMPISTA = 'Centrocampista';
    const POSITION_DELANTERO      = 'Delantero';

    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'The value "{{ value }}" is not valid a position.';
 
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
