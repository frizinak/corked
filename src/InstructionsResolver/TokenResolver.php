<?php


namespace Frizinak\Corked\InstructionsResolver;

use Frizinak\Corked\Cork\Cork;

class TokenResolver extends \Frizinak\Corked\TokensResolver\TokenResolver implements ResolverInterface
{

    public function resolve(Cork $cork)
    {
        $instructions = (array) $cork->getDefinition('instructions')->getValue();
        $tokens = $cork->getTokens();
        foreach ($instructions as &$instruction) {
            $instruction = $this->replaceTokens($instruction, $tokens);
        }

        $cork->getDefinition('instructions')->setValue($instructions);
    }
}
