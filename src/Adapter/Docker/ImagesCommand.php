<?php


namespace Frizinak\Corked\Adapter\Docker;

use Frizinak\Corked\Exception\RuntimeException;

class ImagesCommand extends AbstractCommand
{

    public function execute($all = false, $query = '')
    {
        $builder = $this->getBuilder();
        $args = array('images');
        if ($all) {
            $args[] = '-a';
        }

        if ($query) {
            $args[] = $query;
        }

        $builder->setArguments($args);
        $process = $builder->getProcess();

        $process->mustRun();
        $lines = $this->outputAsArray($process->getOutput());
        if (!count($lines) || !preg_match('/REPOSITORY\s+TAG\s+IMAGE ID\s+CREATED\s+ VIRTUAL SIZE/', $lines[0])) {
            throw new RuntimeException('Unexpected output from `docker images`');
        }

        array_shift($lines);
        $parsed = array();
        $keys = array('repository', 'tag', 'id', 'time ago', 'size');

        $regex = '/^\s*?' .                   //         Leading spaces
                 '(' .                        // * open  Repository (namespace/name)
                 '(?:[a-z0-9_]{4,30}\/)?' .   //         Namespace/
                 '[a-z0-9\-_\.]+' .           // *       Name
                 ')' .                        // * close Repository (namespace/name)
                 '\s+' .                      // *
                 '([a-z0-9_\.\-]{2,30})' .    // *       Tag
                 '\s+' .                      // *
                 '([a-z0-9]+)' .              // *       Image id
                 '\s+' .                      // *
                 '(.*?)' .                    // *       Time ago
                 '\s+' .                      // *
                 '([0-9\.]+ [KMGT]?B)' .      // *       Size
                 '\s*?$/';

        foreach ($lines as $line) {
            if (!preg_match($regex, $line, $matches)) {
                throw new RuntimeException('Unexpected output from `docker images`');
            };
            $parsed[] = array_combine($keys, array_slice($matches, 1));
        }
        return $parsed;
    }
}
