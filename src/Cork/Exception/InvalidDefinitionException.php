<?php


namespace Frizinak\Corked\Cork\Exception;

use Frizinak\Corked\Exception\RuntimeException;

class InvalidDefinitionException extends RuntimeException
{

    public function __construct($message)
    {
        parent::__construct($message);
    }
}
