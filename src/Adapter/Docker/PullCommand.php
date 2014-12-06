<?php


namespace Frizinak\Corked\Adapter\Docker;

class PullCommand extends AbstractCommand
{

    public function execute($namespace, $name, $tag)
    {
        $qualifier = sprintf('%s:%s', $name, $tag);
        if (!empty($namespace)) {
            $qualifier = sprintf('%s/%s', $namespace, $qualifier);
        }
        $args = array('pull', $qualifier);
        $this->getBuilder()->setArguments($args)->getProcess()->mustRun();
    }
}
