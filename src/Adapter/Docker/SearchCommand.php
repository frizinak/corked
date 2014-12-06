<?php


namespace Frizinak\Corked\Adapter\Docker;

use Frizinak\Corked\Exception\RuntimeException;

class SearchCommand extends AbstractCommand
{

    public function execute($query)
    {
        $builder = $this->getBuilder();
        $args = array('search', $query);
        $builder->setArguments($args);
        $process = $builder->getProcess();

        $process->mustRun();
        $lines = $this->outputAsArray($process->getOutput());
        if (!count($lines) || !preg_match('/NAME\s+DESCRIPTION\s+STARS\s+OFFICIAL\s+AUTOMATED/', $lines[0])) {
            throw new RuntimeException('Unexpected output from `docker images`');
        }

        array_shift($lines);
        $parsed = array();
        $keys = array('repository', 'description', 'stars', 'official', 'automated');

        $regex = '/^\s*?' .                   //         Leading spaces
                 '(' .                        // * open  Repository (namespace/name)
                 '(?:[a-z0-9_]{4,30}\/)?' .   //         Namespace/
                 '[a-z0-9\-_\.]+' .           // *       Name
                 ')' .                        // * close Repository (namespace/name)
                 '\s+' .                      // *
                 '(.*?)' .                    // *       Description
                 '\s+' .                      // *
                 '([0-9]+)' .                 // *       Stars
                 '\s+' .                      // *
                 '(\s|\[OK\])' .              // *       Official
                 '\s+' .                      // *
                 '(\s|\[OK\])' .              // *       Automated
                 '\s*?$/';

        foreach ($lines as $line) {
            if (!preg_match($regex, $line, $matches)) {
                throw new RuntimeException('Unexpected output from `docker search`');
            };
            $parsed[] = array_combine($keys, array_slice($matches, 1));
        }
        return $parsed;
    }
}
