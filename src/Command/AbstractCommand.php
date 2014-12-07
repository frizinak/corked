<?php


namespace Frizinak\Corked\Command;

use Frizinak\Corked\Adapter\Docker\CLIAdapter;
use Frizinak\Corked\Corked;
use Frizinak\Corked\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{

    /** @var  InputInterface */
    protected $input;
    protected $basePath;
    protected $corkedPath;

    protected function configure()
    {
        $this->addArgument('base-path', InputArgument::OPTIONAL, 'path to project dir or a corked file.', getcwd())
             ->addOption('docker-client', 'd', InputOption::VALUE_OPTIONAL, 'path to `docker`.', 'docker');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        CLIAdapter::setCli($this->getDockerClient($input));
    }

    protected function getCorked(array $params = array())
    {
        $paths = array();
        if ($basePath = $this->getBasePath()) {
            if (is_file($basePath)) {
                $basePath = dirname($basePath);
            }
            $paths[] = $basePath;
        }

        if ($dockerPathEnv = getenv('DOCKER_PATH')) {
            $paths = array_merge($paths, explode(':', $dockerPathEnv));
        }
        $params += array('lookup_paths' => array());
        $params['lookup_paths'] = array_merge($params['lookup_paths'], $paths);

        return new Corked($params);
    }

    protected function getBasePath()
    {
        if (!$this->basePath) {
            $this->basePath = $basePath = $this->input->getArgument('base-path');
            if (is_file($basePath)) {
                $this->basePath = dirname($basePath);
                $this->corkedPath = $basePath;
            }
        }
        return $this->basePath;
    }

    protected function getCorkedPath()
    {
        if (!$this->corkedPath) {
            $this->getBasePath();
        }

        return $this->corkedPath;
    }

    protected function getDockerClient()
    {
        return $this->input->getOption('docker-client');
    }

    protected function findCorked(Corked $corked)
    {
        foreach ($corked->get('decoders') as $filename => $decoder) {
            $corkedFilePath = $this->getBasePath() . DIRECTORY_SEPARATOR . $filename;
            if ($specifiedPath = $this->getCorkedPath()) {
                if (!preg_match('/' . preg_quote($filename, '/') . '$/', $specifiedPath)) {
                    continue;
                }
                $corkedFilePath = $specifiedPath;
            }

            if (!file_exists($corkedFilePath)) {
                continue;
            }

            if (($data = @file_get_contents($corkedFilePath)) === false) {
                throw new RuntimeException(sprintf('%s could not be read', $corkedFilePath));
            }

            return $corked->get($decoder)->decode($data);
        }
        throw new RuntimeException(sprintf('No corked file could be found in %s', $this->getBasePath()));
    }
}
