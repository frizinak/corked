<?php


namespace Frizinak\Corked\Cork;

use Frizinak\Corked\DependencyResolver\Resolver as DependencyResolver;
use Frizinak\Corked\InstructionsResolver\Resolver as InstructionsResolver;
use Frizinak\Corked\TokensResolver\Resolver as TokensResolver;

class CorkFactory implements CorkFactoryInterface
{

    protected $dependencyResolver;
    protected $instructionsResolver;
    protected $tokensResolver;

    public function __construct(
        DependencyResolver $dependencyResolver,
        InstructionsResolver $instructionsResolver,
        TokensResolver $tokensResolver
    ) {
        $this->dependencyResolver = $dependencyResolver;
        $this->instructionsResolver = $instructionsResolver;
        $this->tokensResolver = $tokensResolver;
    }

    /**
     * @inheritdoc
     */
    public function createCork($data = array())
    {
        $cork = new Cork($this->dependencyResolver, $this->instructionsResolver, $this->tokensResolver, $this);
        foreach ($data as $key => $value) {
            if ($cork->hasDefinition($key)) {
                $cork->getDefinition($key)->setValue($value);
            }
        }

        return $cork;
    }

    /**
     * @inheritdoc
     */
    public function createRoot($data = array())
    {
        $cork = new RootCork($this);
        foreach ($data as $key => $value) {
            if ($cork->hasDefinition($key)) {
                $cork->getDefinition($key)->setValue($value);
            }
        }
        return $cork;
    }
}
