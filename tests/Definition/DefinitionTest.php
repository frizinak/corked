<?php


namespace Frizinak\CorkedTest\Definition;

use Frizinak\Corked\Definition\AbstractDefinition;

class DefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSet()
    {
        /** @var AbstractDefinition $stub */
        $stub = $this->getMockBuilder('Frizinak\\Corked\\Definition\\AbstractDefinition')
                     ->setMethods(array('getDefinitionId', 'validateValue'))
                     ->getMockForAbstractClass();
        $stub->expects($this->never())->method('getDefinitionId');
        $stub->expects($this->once())
             ->method('validateValue')
             ->willReturn('validated')
             ->with($this->equalTo('value'));

        $stub->setValue('value');
        $this->assertEquals('validated', $stub->getValue());
    }

    /**
     * @expectedException \Frizinak\Corked\Definition\Exception\SetValueOnFrozenDefinitionException
     */
    public function testFrozenSet()
    {
        /** @var AbstractDefinition $stub */
        $stub = $this->getMockBuilder('Frizinak\\Corked\\Definition\\AbstractDefinition')
                     ->setMethods(array('getDefinitionId', 'validateValue'))
                     ->getMockForAbstractClass();
        $stub->expects($this->never())->method('getDefinitionId');
        $stub->expects($this->never())->method('validateValue');

        $stub->freeze();
        $this->assertTrue($stub->isFrozen());
        $stub->setValue('value');
    }

    public function testFrozenGet()
    {
        /** @var AbstractDefinition $stub */
        $stub = $this->getMockBuilder('Frizinak\\Corked\\Definition\\AbstractDefinition')
                     ->setMethods(array('getDefinitionId', 'validateValue'))
                     ->getMockForAbstractClass();
        $stub->expects($this->never())->method('getDefinitionId');
        $stub->expects($this->once())
             ->method('validateValue')
             ->willReturn('validated')
             ->with($this->equalTo('value'));

        $stub->setValue('value');
        $stub->freeze();
        $this->assertTrue($stub->isFrozen());
        $this->assertEquals('validated', $stub->getValue());
    }
}
