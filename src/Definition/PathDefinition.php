<?php


namespace Frizinak\Corked\Definition;

use Frizinak\Corked\Definition\Exception\InvalidDefinitionException;

/**
 * Arbitrary filepath container.
 * Valid values are those, when passed through realpath, do not become false.
 */
class PathDefinition extends AbstractDefinition
{

    /**
     * @inheritdoc
     */
    public function getDefinitionId()
    {
        return 'path';
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!($realpath = realpath($value)) || !is_dir($realpath)) {
            throw new InvalidDefinitionException(sprintf('Path definition requires a valid path.'));
        }

        return $realpath;
    }
}
