<?php


namespace Frizinak\Corked\DependencyResolver\Exception;

use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\Exception\ExceptionInterface;
use Frizinak\Corked\Exception\RuntimeException;

class DependencyResolvingFailedException extends RuntimeException
{

    public function __construct(Cork $cork = null, $message = '', \Exception $previous = null)
    {
        $from = $name = 'unknown';
        if ($cork) {
            try {
                $name = $cork->getDefinition('name')->getFullQualifier();
            } catch (ExceptionInterface $e) {
            }
            try {
                $from = $cork->getDefinition('from')->getFullQualifier();
            } catch (ExceptionInterface $e) {
            }
        }

        parent::__construct(
            sprintf("Could not resolve dependency %s for %s." . ($message ? "\n Additionally: " . $message : ''), $from, $name),
            0,
            $previous
        );
    }
}
