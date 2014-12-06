<?php


namespace Frizinak\Corked\InstructionsResolver;

use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\Exception\RuntimeException;

class TokenResolver implements ResolverInterface
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

    protected function replaceTokens($str, $tokens)
    {
        $callback = function ($matches) use ($tokens) {
            $match = trim($matches[1]);
            if (!isset($tokens[$match])) {
                throw new RuntimeException('Unknown token %s', $match);
            }
            return $tokens[$match];
        };

        return preg_replace_callback('/\{\{(.*?)\}\}/', $callback, $str);
    }
}
