<?php


namespace Frizinak\Corked\Adapter;

use Frizinak\Corked\Adapter\Docker\AdapterInterface;
use Frizinak\Corked\Adapter\Docker\CLIAdapter;

class AdapterFactory
{

    /** @var  AdapterInterface */
    protected static $docker;

    public static function getDocker()
    {
        if (!static::$docker) {
            static::$docker = new CLIAdapter();
        }

        return static::$docker;
    }

    public static function setDocker(AdapterInterface $docker)
    {
        static::$docker = $docker;
    }
}
