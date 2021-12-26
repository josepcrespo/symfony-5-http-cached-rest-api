<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ResourceNotFoundException extends Exception
{
  public function __construct(string $resource, string $id)
  {
    $message = "No se encontró un recurso $resource con el ID: $id";
    $code = Response::HTTP_NOT_FOUND;
    parent::__construct($message, $code);
  }
}
