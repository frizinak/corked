<?php


namespace Frizinak\Corked\Command;

use Frizinak\Corked\Cork\Cork;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tests\Fixtures\DummyOutput;

class BuildCommand extends AbstractCommand
{

    protected $pcntl;
    protected $interrupted;
    protected $progressBarWidth = 39;
    protected $progressBar;

    protected function configure()
    {
        $this->setName('build')
             ->addOption('no-cache', null, InputOption::VALUE_NONE, 'Skip docker cache, i.e., rebuild all images.')
             ->addOption('include-path', 'i', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'local repo include paths.')
             ->setDescription('Build images from a corked file');

        parent::configure();
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

        ProgressBar::setFormatDefinition(
            'normal_nomax',
            "<fg=white;bg=green;option=bold> %overall:-7s% %message:50s% </fg=white;bg=green;option=bold> \n %progress% %elapsed:15s% %bar%"
        );

        parent::initialize($input, $output);
    }

    protected function getProgressBar(OutputInterface $output)
    {
        if (!$this->progressBar) {
            $output = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE ? new DummyOutput() : $output;
            $this->progressBar = new ProgressBar($output);
            $this->progressBar->setBarWidth($this->progressBarWidth);
            $this->progressBar->setEmptyBarCharacter(' ');
            $this->progressBar->setProgressCharacter('<fg=green>⬤</fg=green>');
            $this->progressBar->setMessage('?/?', 'overall');
            $this->progressBar->setMessage('?/?', 'progress');
            $this->progressBar->setMessage('');
        }
        return $this->progressBar;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isVebose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $paths = (array) $input->getOption('include-path');

        $corked = $this->getCorked(array('lookup_paths' => $paths));
        $factory = $corked->getFactory();
        $projectCork = $factory->createRoot($this->findCorked($corked));
        $images = $projectCork->getDependents();

        $output->write("\n");
        $progress = $this->getProgressBar($output);

        $current = $maxDepth = 0;
        $toBuild = count($images);
        foreach ($images as $cork) {
            $current && $output->write("\n\n\n");
            $progress->setMessage(sprintf('%d/%d', ++$current, $toBuild), 'overall');
            $callback = function ($depth, Cork $cork, $stdOut, $stdErr) use ($isVebose, $progress, $output, &$maxDepth) {
                $maxDepth = max($maxDepth, $depth);
                if ($this->interrupted) {
                    exit(1);
                }
                if ($isVebose) {
                    $output->write($stdErr);
                    $output->write($stdOut);
                }
                $progress->setMessage(sprintf('%d/%d', $maxDepth - $depth, $maxDepth), 'progress');
                $progress->setMessage($cork->getDefinition('name')->getFullQualifier());
                $progress->advance();
            };

            $cork->build($input->getOption('no-cache'), $callback);
        }

        $output->writeln("\n" . '<info>Success</info>');
    }
}
