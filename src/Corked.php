<?php


namespace Frizinak\Corked;

use Frizinak\Corked\Cork\CorkFactory;
use Frizinak\Corked\Decoder\JsonDecoder;
use Frizinak\Corked\DependencyResolver\FileResolver;
use Frizinak\Corked\DependencyResolver\HubImageResolver;
use Frizinak\Corked\DependencyResolver\LocalImageResolver;
use Frizinak\Corked\DependencyResolver\Resolver as DependencyResolver;
use Frizinak\Corked\Exception\RuntimeException;
use Frizinak\Corked\InstructionsResolver\IncludeResolver;
use Frizinak\Corked\InstructionsResolver\Resolver as InstructionsResolver;
use Frizinak\Corked\InstructionsResolver\TokenResolver;
use Pimple\Container;

class Corked
{

    protected $container;

    /**
     * @param array $params  keys:          use
     *                       lookup_paths   Paths added to the FileResolver
     */
    public function __construct(array $params = array())
    {
        $this->initContainer($params);
    }

    /**
     * @return CorkFactory
     */
    public function getFactory()
    {
        return $this->container['corkfactory'];
    }

    /**
     * Get an item from the container
     *
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->container[$key])) {
            throw new RuntimeException(sprintf('%s was not defined in the container.', $key));
        }

        return $this->container[$key];
    }

    protected function initContainer(array $params = array())
    {
        $this->container = new Container();

        // Params
        $this->container['dependency.resolvers'] = array(
            'dependency.fileresolver',
            'dependency.imageresolver',
            'dependency.hubresolver',
        );

        $this->container['instructions.resolvers'] = array(
            'instructions.tokenresolver',
            'instructions.includeresolver',
            'instructions.tokenresolver',
        );

        $this->container['decoders'] = array(
            'corked.json' => 'decoder.json',
        );

        $params += array('lookup_paths' => array());
        $this->container['dependency.fileresolver.lookup_paths'] = $params['lookup_paths'];

        // Services
        // -- Decoders
        $this->container['decoder.json'] = $this->container->factory(function () {
            // Factory? if not the methods should prolly be static.
            return new JsonDecoder();
        });

        // -- Dependency resolving
        $this->container['dependency.resolver'] = function ($cntnr) {
            $dependencyResolver = new DependencyResolver();
            foreach ($cntnr['dependency.resolvers'] as $resolver) {
                $dependencyResolver->addResolver($cntnr[$resolver]);
            }
            return $dependencyResolver;
        };

        $this->container['dependency.fileresolver'] = function ($cntnr) {
            $resolver = new FileResolver();
            foreach ($cntnr['decoders'] as $filename => $decoder) {
                $resolver->addDecoder($filename, $cntnr[$decoder]);
            }
            foreach ($cntnr['dependency.fileresolver.lookup_paths'] as $lookupPath) {
                $resolver->addLookupPath($lookupPath);
            }
            return $resolver;
        };

        $this->container['dependency.imageresolver'] = function () {
            return new LocalImageResolver();
        };

        $this->container['dependency.hubresolver'] = function () {
            return new HubImageResolver();
        };

        // -- Instructions resolving
        $this->container['instructions.resolver'] = function ($cntnr) {
            $instructionsResolver = new InstructionsResolver();
            foreach ($cntnr['instructions.resolvers'] as $resolver) {
                $instructionsResolver->addResolver($cntnr[$resolver]);
            }
            return $instructionsResolver;
        };

        $this->container['instructions.tokenresolver'] = $this->container->factory(function () {
            return new TokenResolver();
        });

        $this->container['instructions.includeresolver'] = $this->container->factory(function () {
            return new IncludeResolver();
        });

        // -- Corkfactory
        $this->container['corkfactory'] = function ($cntnr) {
            return new CorkFactory($cntnr['dependency.resolver'], $cntnr['instructions.resolver']);
        };
    }
}
