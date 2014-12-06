<?php


namespace Frizinak\Corked\DependencyResolver;

use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\Exception\ExceptionInterface;

/**
 * Dependency resolver interface.
 */
interface ResolverInterface
{

    /**
     * @param Cork $cork
     *
     * @throws ExceptionInterface On failure.
     * @return array|null array on success, null if there was nothing to resolve. (no further dependents)
     *
     * null return might get refactored into the dependee Cork itself.
     */
    public function resolve(Cork $cork);
}
