<?php


namespace Frizinak\Corked\Cork;

use Frizinak\Corked\DependencyResolver\Resolver as DependencyResolver;
use Frizinak\Corked\InstructionsResolver\Resolver as InstructionsResolver;

class CorkFactory implements CorkFactoryInterface
{

    protected $dependencyResolver;
    protected $instructionsResolver;

    public function __construct(DependencyResolver $dependencyResolver, InstructionsResolver $instructionsResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
        $this->instructionsResolver = $instructionsResolver;
    }

    /**
     * @inheritdoc
     */
    public function createCork($data = array())
    {
        $cork = new Cork($this->dependencyResolver, $this->instructionsResolver, $this);
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
