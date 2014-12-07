<?php

namespace Frizinak\Corked\Cork;

use Frizinak\Corked\Adapter\AdapterFactory;
use Frizinak\Corked\Cork\Exception\InvalidDefinitionException;
use Frizinak\Corked\Definition\DefinitionBag;
use Frizinak\Corked\Definition\FromDefinition;
use Frizinak\Corked\Definition\InstructionsDefinition;
use Frizinak\Corked\Definition\NameDefinition;
use Frizinak\Corked\Definition\PathDefinition;
use Frizinak\Corked\Definition\TokensDefinition;
use Frizinak\Corked\DependencyResolver\Resolver as DependencyResolver;
use Frizinak\Corked\Exception\ExceptionInterface;
use Frizinak\Corked\InstructionsResolver\Resolver as InstructionsResolver;
use Frizinak\Corked\TokensResolver\Resolver as TokensResolver;

/**
 * DefinitionBag containing all information necessary to build a docker image.
 * ~= Dockerfile
 */
class Cork extends DefinitionBag
{

    protected $dependent;
    protected $tokens;

    protected $dependencyResolver;
    protected $instructionsResolver;
    protected $tokensResolver;
    protected $factory;

    public function __construct(
        DependencyResolver $dependencyResolver = null,
        InstructionsResolver $instructionResolver = null,
        TokensResolver $tokensResolver = null,
        CorkFactoryInterface $factory = null
    ) {
        $this->dependencyResolver = $dependencyResolver;
        $this->instructionsResolver = $instructionResolver;
        $this->tokensResolver = $tokensResolver;
        $this->factory = $factory;

        foreach ($this->baseDefinitions() as $definition) {
            $this->addDefinition($definition);
        };
    }

    /**
     * Uses the dependencyResolver to find the dependent corkfile or docker-image.
     *
     * @return Cork|false
     */
    public function getDependent()
    {
        if (!isset($this->dependent)) {
            $this->dependent = false;
            if ($resolved = $this->dependencyResolver->resolve($this)) {
                $this->dependent = $this->factory->createCork($resolved);
            }
        }
        return $this->dependent;
    }

    /**
     * Bubble tokens from the dependent onto our TokenDefinition stack.
     * Values are not overwritten, only added.
     *
     * if ns/ubuntu:14.04 were to define the tokens `user.name` as ubuntu and `user.passwd` as abc
     * and dependee ns/ssh-server:1.0 defines `user.passwd` as 123
     * dependees of ns/ssh-server:1.0 will have a user named ubuntu with password 123
     * while the original ns/ubuntu:14.04 image maintains its own password (abc).
     *
     * @return array
     */
    public function getTokens()
    {
        $def = $this->getDefinition('tokens');
        if ($def->isFrozen()) {
            return $def->getValue();
        }

        $this->tokensResolver->resolve($this);

        return $def->freeze()->getValue();
    }

    /**
     * Uses the instructionsResolver to resolve InstructionsDefinition into valid Dockerfile commands
     * The FromDefinition should be modified directly to allow incremental resolves.
     *
     * @return array
     */
    public function getInstructions()
    {
        $def = $this->getDefinition('instructions');
        if ($def->isFrozen()) {
            return $def->getValue();
        }

        $this->instructionsResolver->resolve($this);

        return $def->freeze()->getValue();
    }

    public function build($force = false, \Closure $callback = null, $depth = 0)
    {
        if ($dependent = $this->getDependent()) {
            $dependent->build($force, $callback, $depth + 1);
        }

        if (!$this->getDefinition('from')->getValue()) {
            return;
        }

        $callbackWrap = null;
        if ($callback) {
            $self = $this;
            $callbackWrap = function ($stdOut, $stdErr) use ($self, $callback, $depth) {
                $callback($depth, $self, $stdOut, $stdErr);
            };
        }

        AdapterFactory::getDocker()->build(
            $this->getDefinition('name')->getFullQualifier(),
            $this->toDockerString(),
            $this->getDefinition('path')->getValue(),
            $callbackWrap,
            $force
        );
    }

    protected function toDockerString()
    {
        /** @var FromDefinition $from */
        $from = $this->getDefinition('from');
        try {
            $qualifier = $from->getFullQualifier();
        } catch (ExceptionInterface $e) {
            $name = @$this->getDefinition('name')->getFullQualifier();
            throw new InvalidDefinitionException(sprintf(
                'Cork %s can not be converted to a string as the FROM definition is invalid.',
                $name ? $name : 'unknown'
            ));
        }
        return sprintf("FROM %s\n\n%s", $qualifier, implode("\n", $this->getInstructions()));
    }

    /**
     * Returns our default definitions.
     */
    protected function baseDefinitions()
    {
        return array(
            new NameDefinition(),  // Our qualifier
            new FromDefinition(),  // Dependent qualifier
            new InstructionsDefinition(),
            new TokensDefinition(),
            new PathDefinition(),  // Our path (value empty if not applicable)
        );
    }
}
