<?php


namespace Frizinak\Corked\TokensResolver;

use Frizinak\Corked\Cork\Cork;

interface ResolverInterface
{

    public function resolve(Cork $cork);
}
