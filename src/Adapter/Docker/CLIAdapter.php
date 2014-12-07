<?php


namespace Frizinak\Corked\Adapter\Docker;

use Symfony\Component\Process\ProcessBuilder;

class CLIAdapter implements AdapterInterface
{

    protected static $cli = 'docker';
    /** @var  ProcessBuilder */
    protected $processBuilder;

    public static function setCli($cli)
    {
        static::$cli = $cli;
    }

    /**
     * @inheritdoc
     */
    public function imageExistsLocally($namespace, $name, $tag)
    {
        $repo = empty($namespace) ? $name : sprintf('%s/%s', $namespace, $name);
        $images = $this->images(false, $repo);
        foreach ($images as $image) {
            if ($image['repository'] == $repo && $image['tag'] == $tag) {
                return true;
            }
        }

        return false;
    }

    public function images($all = false, $query = '')
    {
        $command = new ImagesCommand(static::$cli);
        return $command->execute($all, $query);
    }

    /**
     * @inheritdoc
     */
    public function imageExistsOnHub($namespace, $name, $tag)
    {
        $repo = empty($namespace) ? $name : sprintf('%s/%s', $namespace, $name);
        $images = $this->search($repo);
        foreach ($images as $image) {
            // No tag check, assume the image exist?
            // Best we can do with the cli atm. :(
            if ($image['repository'] == $repo) {
                return true;
            }
        }

        return false;
    }

    public function search($query)
    {
        $command = new SearchCommand(static::$cli);
        return $command->execute($query);
    }

    /**
     * @inheritdoc
     */
    public function pull($namespace, $name, $tag)
    {
        $command = new PullCommand(static::$cli);
        $command->execute($namespace, $name, $tag);
    }

    /**
     * @inheritdoc
     */
    public function build($qualifier, $dockerFileContents, $path, \Closure $callback = null, $skipCache = false)
    {
        $command = new BuildCommand(static::$cli);
        $command->execute($qualifier, $dockerFileContents, $path, $callback, $skipCache);
    }

    protected function getBuilder()
    {
        if (!isset($this->processBuilder)) {
            $this->processBuilder = new ProcessBuilder();
        }

        return $this->processBuilder->setPrefix(static::$cli);
    }

    protected function outputAsArray($out)
    {
        return array_filter(explode("\n", preg_replace("/\r+|\n+/i", "\n", $out)));
    }
}
