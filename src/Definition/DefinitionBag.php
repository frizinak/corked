<?php


namespace Frizinak\Corked\Definition;

use Frizinak\Corked\Definition\Exception\InvalidDefinitionException;
use Frizinak\Corked\Exception\InvalidArgumentException;
use Frizinak\Corked\Exception\RuntimeException;

/**
 * DefinitionInterface collection.
 * Only a single DefinitionInterface can be stored per DefinitionInterface::getDefinitionId().
 */
class DefinitionBag
{

    /** @var DefinitionInterface[] */
    protected $definitions;

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasDefinition($name)
    {
        return isset($this->definitions[$name]);
    }

    /**
     * @param $name
     *
     * @return DefinitionInterface
     */
    public function getDefinition($name)
    {
        if (!isset($this->definitions[$name])) {
            throw new RuntimeException(sprintf('%s definition was never set.', $name));
        }
        return $this->definitions[$name];
    }

    public function addDefinition(DefinitionInterface $definition)
    {
        $identifier = $definition->getDefinitionId();

        if (!$identifier) {
            throw new InvalidDefinitionException('Definition id can not be empty');
        }

        if (isset($this->definitions[$identifier])) {
            throw new InvalidArgumentException(sprintf('A definition for %s has already been set', $identifier));
        }

        $this->definitions[$identifier] = $definition;
    }
}
