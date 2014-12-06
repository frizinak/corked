<?php


namespace Frizinak\Corked\InstructionsResolver;

use Frizinak\Corked\Cork\Cork;

interface ResolverInterface
{

    public function resolve(Cork $cork);
}
