<?php


namespace Frizinak\Corked\DependencyResolver;

use Frizinak\Corked\Adapter\Docker\AdapterInterface;

class HubImageResolver extends AbstractDockerResolver
{

    /**
     * Searches the docker hub for an image.
     *
     * @inheritdoc
     */
    protected function exists(AdapterInterface $adapter, $namespace, $name, $tag)
    {
        return $adapter->imageExistsOnHub($namespace, $name, $tag);
    }
}
