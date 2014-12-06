<?php


namespace Frizinak\Corked\Adapter\Docker;

use Symfony\Component\Process\ProcessBuilder;

abstract class AbstractCommand
{

    protected $cli;

    /**
     * @param string $cli Path to the docker cli
     */
    public function __construct($cli = 'docker')
    {
        $this->cli = $cli;
    }

    /**
     * @return ProcessBuilder
     */
    protected function getBuilder()
    {
        if (!isset($this->processBuilder)) {
            $this->processBuilder = new ProcessBuilder();
        }

        return $this->processBuilder->setPrefix($this->cli);
    }

    /**
     * Return as an array where each entry is a line.
     *
     * @param string $out
     *
     * @return array
     */
    protected function outputAsArray($out)
    {
        return array_filter(explode("\n", $this->cleanOutput($out)));
    }

    /**
     * Convert a non-LF string to LF.
     *
     * @param string $out
     *
     * @return mixed
     */
    protected function cleanOutput($out)
    {
        return preg_replace("/\r+|\n+/i", "\n", $out);
    }
}
