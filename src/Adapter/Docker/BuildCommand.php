<?php


namespace Frizinak\Corked\Adapter\Docker;

use Frizinak\Corked\Exception\InvalidArgumentException;
use Frizinak\Corked\Exception\RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BuildCommand extends AbstractCommand
{

    public function execute($qualifier, $dockerFileContents, $path, \Closure $callback = null, $skipCache = false)
    {
        if (($realpath = realpath($path)) === false || !is_dir($realpath)) {
            throw new InvalidArgumentException(sprintf('The path %s does not point to a valid directory', $realpath));
        }
        $dockerFile = $realpath . DIRECTORY_SEPARATOR . 'Dockerfile';
        if (file_exists($dockerFile)) {
            throw new RuntimeException(
                sprintf('A Dockerfile exists in %s, please rename this file as docker does not allow us to specify a custom path.', $realpath)
            );
        }

        if (@file_put_contents($dockerFile, $dockerFileContents) === false) {
            throw new RuntimeException(sprintf('Dockerfile could not be created at %s', $realpath));
        }

        register_shutdown_function(function () use ($dockerFile) {
            @unlink($dockerFile);
        });

        $args = array('build', '--force-rm=true', sprintf('--tag=%s', $qualifier));
        if ($skipCache) {
            $args[] = '--no-cache=true';
        }
        $args[] = $realpath;
        $process = $this->getBuilder()->setArguments($args)->getProcess();

        $process->start();
        while ($process->isRunning()) {
            if ($callback) {
                $callback($this->cleanOutput($process->getIncrementalOutput()), $this->cleanOutput($process->getIncrementalErrorOutput()));
            }
            usleep(50000);
        }

        @unlink($dockerFile);
        if ($process->getExitCode()) {
            throw new ProcessFailedException($process);
        }
    }
}
