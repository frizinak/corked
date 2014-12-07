<?php


namespace Frizinak\Corked\TokensResolver;

use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\Exception\RuntimeException;

class TokenResolver implements ResolverInterface
{

    public function resolve(Cork $cork)
    {
        $tokens = (array) $cork->getDefinition('tokens')->getValue();

        $maxDepth = 0;
        do {
            $changed = false;
            foreach ($tokens as &$value) {
                $newvalue = $this->replaceTokens($value, $tokens);
                $changed = $changed || $newvalue != $value;
                $value = $newvalue;
            }
            if (++$maxDepth > 512) {
                throw new RuntimeException('There seems to be a circular reference in the specified tokens');
            }
        } while ($changed);

        $cork->getDefinition('tokens')->setValue($tokens);
    }

    public function replaceTokens($str, $tokens)
    {
        $callback = function ($matches) use ($tokens) {
            $match = trim($matches[1]);
            if (!isset($tokens[$match])) {
                throw new RuntimeException(sprintf('Unknown token `%s`', $match));
            }
            return $tokens[$match];
        };

        return preg_replace_callback('/\{\{(.*?)\}\}/', $callback, $str);
    }
}
