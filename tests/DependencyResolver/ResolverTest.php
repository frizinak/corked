<?php


namespace Frizinak\CorkedTest\DependencyResolver;

use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\DependencyResolver\Exception\DependencyResolvingFailedException;
use Frizinak\Corked\DependencyResolver\Resolver;

class ResolverTest extends \PHPUnit_Framework_TestCase
{

    protected function getResolver()
    {
        return $this->getMockBuilder('Frizinak\\Corked\\DependencyResolver\\ResolverInterface')->getMock();
    }

    protected function getCompatibleResolver($arg, $return)
    {
        $resolver = $this->getResolver();
        $resolver->expects($this->once())->method('resolve')->with($arg)->willReturn($return);
        return $resolver;
    }

    protected function getIncompatibleResolver($arg)
    {
        $resolver = $this->getResolver();
        $resolver->expects($this->once())->method('resolve')->with($arg)
                 ->willThrowException(new DependencyResolvingFailedException());
        return $resolver;
    }

    protected function getNeverCalledResolver()
    {
        $resolver = $this->getResolver();
        $resolver->expects($this->never())->method('resolve');
        return $resolver;
    }

    public function testNoDependencies()
    {
        $cork = new Cork();

        $dependencyResolver = new Resolver();
        $dependencyResolver->addResolver($this->getIncompatibleResolver($cork));
        $dependencyResolver->addResolver($this->getIncompatibleResolver($cork));
        $dependencyResolver->addResolver($this->getCompatibleResolver($cork, null));
        $dependencyResolver->addResolver($this->getNeverCalledResolver($cork));

        $this->assertNull($dependencyResolver->resolve($cork));
    }

    public function testDependencies()
    {
        $cork = new Cork();
        $dependencyResolver = new Resolver();
        $dependencyResolver->addResolver($this->getIncompatibleResolver($cork));
        $dependencyResolver->addResolver($this->getIncompatibleResolver($cork));
        $dependencyResolver->addResolver($this->getCompatibleResolver($cork, array('name' => 'wop')));
        $dependencyResolver->addResolver($this->getNeverCalledResolver($cork));

        $this->assertEquals(array('name' => 'wop'), $dependencyResolver->resolve($cork));
    }

    /**
     * @expectedException \Frizinak\Corked\Exception\ExceptionInterface
     */
    public function testNoCompatibleResolver()
    {
        $cork = new Cork();
        $dependencyResolver = new Resolver();
        $dependencyResolver->addResolver($this->getIncompatibleResolver($cork));
        $dependencyResolver->addResolver($this->getIncompatibleResolver($cork));
        $dependencyResolver->addResolver($this->getIncompatibleResolver($cork));

        $dependencyResolver->resolve($cork);
    }
}
