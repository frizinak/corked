<?php


namespace Frizinak\Corked\DependencyResolver;

use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\DependencyResolver\Exception\DependencyResolvingFailedException;
use Frizinak\Corked\Exception\ExceptionInterface;

/**
 * DependencyResolver collection.
 *
 * First resolver that succeeds wins.
 *
 * Resolvers should return void if there was nothing to resolve and
 * an exception ought to be thrown if the resolver fails.
 */
class Resolver
{

    /** @var ResolverInterface[] $resolvers */
    protected $resolvers;

    public function addResolver(ResolverInterface $resolver)
    {
        $this->resolvers[] = $resolver;
    }

    public function resolve(Cork $cork)
    {
        foreach ($this->resolvers as $resolver) {
            try {
                return $resolver->resolve($cork);
            } catch (DependencyResolvingFailedException $e) {
            } catch (ExceptionInterface $e) {
                throw new DependencyResolvingFailedException($cork, '', $e);
            }
        }

        throw new DependencyResolvingFailedException($cork);
    }
}
