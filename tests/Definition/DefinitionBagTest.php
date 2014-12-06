<?php


namespace Frizinak\CorkedTest\Definition;

use Frizinak\Corked\Definition\DefinitionBag;
use Frizinak\Corked\Exception\ExceptionInterface;

class DefinitionBagTest extends \PHPUnit_Framework_TestCase
{

    public function testHasDefinition()
    {
        $bag = new DefinitionBag();
        $bag->addDefinition($this->getDefinitionMock('test'));
        $bag->addDefinition($this->getDefinitionMock('test2'));

        $this->assertTrue($bag->hasDefinition('test'));
        $this->assertTrue($bag->hasDefinition('test2'));
        $this->assertFalse($bag->hasDefinition('test3'));
    }

    protected function getDefinitionMock($identifier)
    {
        $stub = $this->getMockBuilder('Frizinak\\Corked\\Definition\\DefinitionInterface')
                     ->getMock();
        $stub->method('getDefinitionId')->willReturn($identifier);
        return $stub;
    }

    public function testAddDefinition()
    {
        $bag = new DefinitionBag();
        $bag->addDefinition($this->getDefinitionMock('test'));
        $bag->addDefinition($this->getDefinitionMock('test2'));
        $exception = null;
        try {
            $bag->addDefinition($this->getDefinitionMock('test2'));
        } catch (ExceptionInterface $exception) {
        }

        $this->assertNotNull($exception);

        $exception = null;
        try {
            $bag->addDefinition($this->getDefinitionMock(''));
        } catch (ExceptionInterface $exception) {
        }

        $this->assertNotNull($exception);
    }

    public function testGetDefinition()
    {
        $bag = new DefinitionBag();

        $def1 = $this->getDefinitionMock('test');

        $bag->addDefinition($def1);

        $this->assertSame($def1, $bag->getDefinition('test'));

        $exception = null;
        try {
            $bag->getDefinition('test2');
        } catch (ExceptionInterface $exception) {
        }

        $this->assertNotNull($exception);
    }
}
