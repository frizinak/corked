<?php


namespace Frizinak\Corked\DependencyResolver;

use Frizinak\Corked\Adapter\AdapterFactory;
use Frizinak\Corked\Adapter\Docker\AdapterInterface;
use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\Definition\FromDefinition;
use Frizinak\Corked\DependencyResolver\Exception\DependencyResolvingFailedException;

/**
 * Abstract docker resolver.
 * Dependents resolved against this resolver only contain information about their name.
 */
abstract class AbstractDockerResolver implements ResolverInterface
{

    /**
     * @inheritdoc
     */
    public function resolve(Cork $cork)
    {
        /** @var FromDefinition $from */
        $from = $cork->getDefinition('from');
        if (!$from->getValue()) {
            return null;
        }

        $namespace = $from->getNamespace();
        $name = $from->getName();
        $tag = $from->getTag();

        $adapter = AdapterFactory::getDocker();
        if ($this->exists($adapter, $namespace, $name, $tag)) {
            return array('name' => $from->getFullQualifier());
        }

        throw new DependencyResolvingFailedException();
    }

    /**
     * Returns whether the requested qualifier exists for the given DockerAdapterInterface.
     *
     * @param AdapterInterface $adapter
     * @param string           $namespace
     * @param string           $name
     * @param string           $tag
     *
     * @return bool
     */
    abstract protected function exists(AdapterInterface $adapter, $namespace, $name, $tag);
}
