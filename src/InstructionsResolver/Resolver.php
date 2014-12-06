<?php


namespace Frizinak\Corked\InstructionsResolver;

use Frizinak\Corked\Cork\Cork;

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
            $resolver->resolve($cork);
        }
    }
}
