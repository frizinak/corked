<?php


namespace Frizinak\Corked\Definition;

/**
 * FromDefinition container.
 * Input is an array where each element could be passed to FromDefinition.
 */
class ImagesDefinition extends AbstractDefinition
{

    /**
     * @return string
     */
    public function getDefinitionId()
    {
        return "images";
    }

    protected function validateValue($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        $fromDefinitions = array();
        foreach ($value as $val) {
            $fromDefinition = new FromDefinition();
            $fromDefinition->setValue($val);
            $fromDefinitions[] = $fromDefinition;
        }

        return $fromDefinitions;
    }
}
