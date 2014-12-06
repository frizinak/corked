<?php


namespace Frizinak\Corked\Definition\Exception;

use Frizinak\Corked\Exception\RuntimeException;

class SetValueOnFrozenDefinitionException extends RuntimeException
{

    public function __construct()
    {
        parent::__construct('Can not set a value on a frozen definition.');
    }
}
