<?php


namespace Frizinak\Corked\Command;

use Frizinak\Corked\Adapter\Docker\CLIAdapter;
use Frizinak\Corked\Corked;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{

    /** @var  InputInterface */
    protected $input;

    protected function configure()
    {
        $this->addArgument('base-path', InputArgument::OPTIONAL, 'path to project dir (where your main corked file resides).', getcwd())
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
        return $this->input->getArgument('base-path');
    }

    protected function getDockerClient()
    {
        return $this->input->getOption('docker-client');
    }
}
