<?php


namespace Frizinak\Corked\Adapter\Docker;

interface AdapterInterface
{

    /**
     * @param string $namespace
     * @param string $name
     * @param string $tag
     *
     * @return bool
     */
    public function imageExistsLocally($namespace, $name, $tag);

    /**
     * @param string $namespace
     * @param string $name
     * @param string $tag
     *
     * @return bool
     */
    public function imageExistsOnHub($namespace, $name, $tag);

    /**
     * @param string $namespace
     * @param string $name
     * @param string $tag
     *
     * @return void
     */
    public function pull($namespace, $name, $tag);

    /**
     * @param string   $qualifier          The desired image qualifier (ns/name:tag)
     * @param string   $dockerFileContents Contents of the Dockerfile
     * @param string   $path               The docker Build context path. @see https://docs.docker.com/reference/builder/
     * @param \Closure $callback           Optional callback that will be called intermittently with $stdOut and $stdErr.
     * @param bool     $skipCache          Force rebuild the image.
     */
    public function build($qualifier, $dockerFileContents, $path, \Closure $callback = null, $skipCache = false);
}
