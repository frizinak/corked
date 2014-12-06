<?php


namespace Frizinak\Corked\Definition;

use Frizinak\Corked\Definition\Exception\SetValueOnFrozenDefinitionException;

/**
 * Basic DefinitionInterface implementation that allows
 * validation/parsing of the value before it can be set,
 * and enforces the freeze method.
 */
abstract class AbstractDefinition implements DefinitionInterface
{

    protected $value;
    protected $initialized = false;
    protected $frozen;

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->initialized = false;
        if ($this->frozen) {
            throw new SetValueOnFrozenDefinitionException();
        }

        $this->value = $this->validateValue($value);
        $this->initialized = true;
        return $this;
    }

    abstract protected function validateValue($value);

    final public function isFrozen()
    {
        return $this->frozen;
    }

    final public function freeze()
    {
        $this->frozen = true;
        return $this;
    }
}
