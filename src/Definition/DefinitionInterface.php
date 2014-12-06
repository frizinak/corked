<?php


namespace Frizinak\Corked\Definition;

/**
 * Arbitrary data container.
 */
interface DefinitionInterface
{

    /**
     * @return string
     */
    public function getDefinitionId();

    /**
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value);

    /**
     * @return $this
     */
    public function freeze();

    /**
     * @return bool
     */
    public function isFrozen();

    /**
     * @return mixed
     */
    public function getValue();
}
