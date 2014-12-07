<?php


namespace Frizinak\Corked\TokensResolver;

use Frizinak\Corked\Cork\Cork;

class InheritanceResolver implements ResolverInterface
{

    public function resolve(Cork $cork)
    {
        $tokens = (array) $cork->getDefinition('tokens')->getValue();
        if ($dependent = $cork->getDependent()) {
            $tokens += (array) $dependent->getTokens();
        }

        $cork->getDefinition('tokens')->setValue($tokens);
    }
}
