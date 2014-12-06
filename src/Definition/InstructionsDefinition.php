<?php


namespace Frizinak\Corked\Definition;

use Frizinak\Corked\Definition\Exception\ValidationException;

/**
 * Docker instructions container.
 * Valid values:
 *  - a string
 *  - an array of strings
 *  - an array of array of string
 */
class InstructionsDefinition extends AbstractDefinition
{

    /**
     * @inheritdoc
     */
    public function getDefinitionId()
    {
        return 'instructions';
    }

    protected function validateValue($value)
    {
        if (is_string($value)) {
            return array($value);
        }

        $flat = array();
        if (is_array($value)) {
            foreach ($value as $entry) {
                $flat = array_merge($flat, $this->getNormalizedEntry($entry));
            }
            return $flat;
        }

        throw new ValidationException('Instructions should be either strings or traversables');
    }

    protected function getNormalizedEntry($entry)
    {
        if (!is_string($entry) && !is_array($entry)) {
            throw new ValidationException('Instructions should be either strings or traversables');
        }
        $entry = (array) $entry;
        $count = count($entry);
        foreach ($entry as $k => $subentry) {
            if (!is_string($subentry)) {
                throw new ValidationException('Instructions should be no deeper than 2 traversables.');
            }
            --$count && $entry[$k] .= ' \\';
        }
        return $entry;
    }
}
