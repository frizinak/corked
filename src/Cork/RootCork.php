<?php


namespace Frizinak\Corked\Cork;

use Frizinak\Corked\Definition\DefinitionBag;
use Frizinak\Corked\Definition\FromDefinition;
use Frizinak\Corked\Definition\ImagesDefinition;
use Frizinak\Corked\Exception\RuntimeException;

/**
 * Project level definitionBag.
 *
 * Currently only holds the images to build. (ImagesDefinition)
 * But will soon contain container and system definitions.
 *
 * ~= Main orchestrator
 */
class RootCork extends DefinitionBag
{

    protected $factory;

    public function __construct(CorkFactoryInterface $factory)
    {
        $this->factory = $factory;
        foreach ($this->baseDefinitions() as $definition) {
            $this->addDefinition($definition);
        }
    }

    /**
     * Creates dummy corks from the ImagesDefinition, which are resolved and returned.
     *
     * @see Cork::getDependent();
     * @return Cork[]
     */
    public function getDependents()
    {
        $dependents = array();
        $images = $this->getDefinition('images')->getValue();
        if (!is_array($images)) {
            throw new RuntimeException('The images definition was never initialized');
        }

        /** @var FromDefinition $fromDefinition */
        foreach ($images as $fromDefinition) {
            $cork = $this->factory->createCork(array('from' => $fromDefinition->getFullQualifier()));
            if ($dependent = $cork->getDependent()) {
                $dependents[] = $dependent;
            }
        }
        return $dependents;
    }

    /**
     * Returns our default definitions.
     */
    protected function baseDefinitions()
    {
        return array(
            new ImagesDefinition() // Array of from definitions.
        );
    }
}
