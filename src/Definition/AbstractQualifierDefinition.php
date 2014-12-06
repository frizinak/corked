<?php

namespace Frizinak\Corked\Definition;

use Frizinak\Corked\Definition\Exception\QualifierValidationException;
use Frizinak\Corked\Exception\InvalidArgumentException;

/**
 * Docker image qualifier container.
 *
 * Values have to be in any of these formats:
 * name                - official repos only
 * name:tag            - official repos only
 * namespace/name      - tag will be set to AbstractQualifierDefinition::TAG_LATEST.
 * namespace/name:tag
 */
abstract class AbstractQualifierDefinition extends AbstractDefinition
{

    const TAG_LATEST = 'latest';

    public function getNamespace()
    {
        return $this->getQualifierPart(0);
    }

    protected function getQualifierPart($part)
    {
        $value = $this->getValue();
        if (!isset($value[$part])) {
            throw new InvalidArgumentException(sprintf('part %s does not exist', $part));
        }
        return $value[$part];
    }

    public function getName()
    {
        return $this->getQualifierPart(1);
    }

    public function getFullQualifier()
    {
        return sprintf('%s:%s', $this->getRepository(), $this->getTag());
    }

    public function getRepository()
    {
        list($namespace, $name) = $this->getValue();
        $repository = $name;
        if ($namespace) {
            $repository = sprintf('%s/%s', $namespace, $repository);
        }

        return $repository;
    }

    public function getTag()
    {
        return $this->getQualifierPart(2);
    }

    protected function validateValue($value)
    {
        if (!is_string($value)) {
            throw new QualifierValidationException(sprintf('The given `%s` qualifier is not a string', $this->getDefinitionId()));
        }

        $parts = $this->extractQualifierParts($value);

        if (!empty($parts[0]) && !preg_match('/^[a-z0-9_]{4,30}$/', $parts[0])) {
            throw new QualifierValidationException('Namespace should only contain [a-z0-9_] and have a length between 4 and 30 characters', 1);
        }

        if (!preg_match('/^[a-z0-9\-_\.]+$/', $parts[1])) {
            throw new QualifierValidationException('Name should only contain [a-z0-9-_.]', 2);
        }

        if (!preg_match('/^[a-z0-9_\.\-]{2,30}$/i', $parts[2])) {
            throw new QualifierValidationException('Tag should only contain [A-Za-z0-9-_.] and have a length between 2 and 30 characters', 3);
        }

        return $parts;
    }

    protected function extractQualifierParts($qualifier)
    {
        $parts = explode('/', $qualifier);
        $partCount = count($parts);

        if ($partCount > 2) {
            throw new QualifierValidationException('Too many slashes', 0);
        }

        if ($partCount < 2) {
            // Official docker repos.
            array_unshift($parts, '');
        }

        $nameParts = explode(':', $parts[1]);
        if (count($nameParts) > 2) {
            throw new QualifierValidationException(
                sprintf('Invalid tag %s', implode(':', array_slice($nameParts, 1))),
                3
            );
        }

        $nameParts[] = static::TAG_LATEST;
        $parts[2] = $nameParts[1];
        $parts[1] = $nameParts[0];

        return $parts;
    }
}
