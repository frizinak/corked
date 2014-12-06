<?php


namespace Frizinak\Corked\DependencyResolver;

use Frizinak\Corked\Adapter\Docker\AdapterInterface;

class LocalImageResolver extends AbstractDockerResolver
{

    /**
     * Searches the local set of `docker images`
     *
     * @inheritdoc
     */
    protected function exists(AdapterInterface $adapter, $namespace, $name, $tag)
    {
        return $adapter->imageExistsLocally($namespace, $name, $tag);
    }
}
