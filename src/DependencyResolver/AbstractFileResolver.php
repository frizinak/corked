<?php


namespace Frizinak\Corked\DependencyResolver;

use Frizinak\Corked\Exception\InvalidArgumentException;

/**
 * Maintains a set of directory paths extenders ought to limit their search to.
 */
abstract class AbstractFileResolver implements ResolverInterface
{

    protected $lookupPaths = array();

    /**
     * @param string $path A valid directory path
     */
    public function addLookupPath($path)
    {
        if (!($realpath = realpath($path)) || !is_dir($realpath)) {
            throw new InvalidArgumentException(sprintf('The path %s does not exist.', $path));
        }

        $this->lookupPaths[] = $realpath;
    }
}
