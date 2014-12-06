<?php


namespace Frizinak\Corked\Command;

use Frizinak\Corked\Adapter\Docker\CLIAdapter;
use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\Corked;
use Frizinak\Corked\Decoder\JsonDecoder;
use Frizinak\Corked\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{

    protected $client;
    protected $pcntl;
    protected $interrupted;
    protected $progressBarWidth = 39;

    public function __construct()
    {
        ProgressBar::setFormatDefinition(
            'normal_nomax',
            "<fg=white;bg=green;option=bold> %overall:-7s% %message:50s% </fg=white;bg=green;option=bold> \n %progress% %elapsed:15s% %bar%"
        );

        parent::__construct('Corked');
    }

    protected function configure()
    {
        $this->setName('build')
             ->addArgument('base-path', InputArgument::OPTIONAL, 'path to project dir (where your main corked file resides).', getcwd())
             ->addOption('docker-client', 'd', InputOption::VALUE_OPTIONAL, 'path to `docker`.', 'docker')
             ->addOption('no-cache', null, InputOption::VALUE_NONE, 'Skip docker cache, i.e., rebuild all images.')
             ->addOption('include-path', 'i', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'local repo include paths.')
             ->setDescription('Build images from a corked.json');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $self = $this;
        $this->pcntl = function_exists('pcntl_signal');
        if ($this->pcntl) {
            $callback = function () use ($output, $self) {
                $self->interrupted = true;
            };
            pcntl_signal(SIGTERM, $callback);
            pcntl_signal(SIGHUP, $callback);
            pcntl_signal(SIGINT, $callback);
        } else {
            $output->writeln('<error>pcntl not available.</error>');
            $output->writeln('<comment>Until docker accepts https://github.com/docker/docker/issues/2112</comment>');
            $output->writeln('<comment>We need to create an intermediate Dockerfile inside each context dir.</comment>');
            $output->writeln('<comment>The pcntl signal trap allows us to clean up if the build was interrupted.</comment>' . "\n");
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = (array) $input->getOption('include-path');
        if ($corkedPath = $input->getArgument('base-path')) {
            $paths[] = $corkedPath;
        }

        if ($dockerPathEnv = getenv('DOCKER_PATH')) {
            $paths = array_merge($paths, explode(':', $dockerPathEnv));
        }

        $corkedFilePath = $corkedPath . DIRECTORY_SEPARATOR . 'corked.json';

        $corked = new Corked(array('lookup_paths' => $paths));
        /** @var JsonDecoder $jsonDecoder */
        $jsonDecoder = $corked->get('decoder.json');

        if (!file_exists($corkedFilePath)) {
            throw new RuntimeException(sprintf('No corked.json file could be found %s', $corkedPath));
        }

        if (($data = @file_get_contents($corkedFilePath)) === false) {
            throw new RuntimeException(sprintf('%s could not be read', $corkedFilePath));
        }

        $factory = $corked->getFactory();

        $output->writeln('');
        $progress = null;
        if ($output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            $progress = new ProgressBar($output);
            $progress->setBarWidth($this->progressBarWidth);
            $progress->setEmptyBarCharacter(' ');
            $progress->setProgressCharacter('<fg=green>⬤</fg=green>');
        }

        $projectCork = $factory->createRoot($jsonDecoder->decode($data));

        CLIAdapter::setCli($input->getOption('docker-client'));
        $maxDepth = 0;
        $images = $projectCork->getDependents();
        $toBuild = count($images);
        $current = 0;
        foreach ($images as $cork) {
            if ($progress) {
                $progress->start();
                $progress->setMessage(sprintf('%d/%d', ++$current, $toBuild), 'overall');
            }

            $callback = function ($depth, Cork $cork, $stdOut, $stdErr) use ($progress, $output, &$maxDepth) {
                $maxDepth = max($maxDepth, $depth);
                if ($this->interrupted) {
                    exit(1);
                }
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $output->write($stdErr);
                    $output->write($stdOut);
                }
                if ($progress) {
                    $progress->setMessage(sprintf('%d/%d', $maxDepth - $depth, $maxDepth), 'progress');
                    $progress->setMessage($cork->getDefinition('name')->getFullQualifier());
                    $progress->advance();
                }
            };

            $cork->build($input->getOption('no-cache'), $callback);
        }

        $output->writeln("\n" . '<info>Success</info>');
    }
}
