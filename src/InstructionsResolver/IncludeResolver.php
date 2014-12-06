<?php


namespace Frizinak\Corked\InstructionsResolver;

use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\Exception\RuntimeException;

class IncludeResolver implements ResolverInterface
{

    public function resolve(Cork $cork)
    {
        $instructions = (array) $cork->getDefinition('instructions')->getValue();
        $path = $cork->getDefinition('path')->getValue();
        $flat = array();
        foreach ($instructions as $instruction) {
            $flat = array_merge($flat, explode("\n", $this->replaceIncludes($instruction, $path)));
        }

        $cork->getDefinition('instructions')->setValue($flat);
    }

    protected function replaceIncludes($str, $path)
    {
        $callback = function ($matches) use ($path) {
            if (!$path) {
                throw new RuntimeException('Invalid path');
            }
            $filepath = $path . DIRECTORY_SEPARATOR . $matches[1];
            if (!file_exists($filepath) || false === ($contents = file_get_contents($filepath))) {
                throw new RuntimeException(sprintf('Could not read %s', $filepath));
            }

            return $contents;
        };

        return preg_replace_callback('/^\s*?INCLUDE ([^#]*).*?$/i', $callback, $str);
    }
}
