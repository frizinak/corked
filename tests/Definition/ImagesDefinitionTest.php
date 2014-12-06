<?php


namespace Frizinak\CorkedTest\Definition;

use Frizinak\Corked\Definition\FromDefinition;
use Frizinak\Corked\Definition\ImagesDefinition;

class ImagesDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function testSingleValue()
    {
        $def = new ImagesDefinition();
        $def->setValue('namespace/name:tag');
        $value = $def->getValue();

        $this->assertTrue(is_array($value));
        $this->assertCount(1, $value);
        $this->assertInstanceOf('Frizinak\\Corked\\Definition\\FromDefinition', $value[0]);

        $fromDef = new FromDefinition();
        $fromDef->setValue('namespace/name:tag');
        $this->assertEquals($fromDef->getNamespace(), $value[0]->getNamespace());
        $this->assertEquals($fromDef->getName(), $value[0]->getName());
        $this->assertEquals($fromDef->getTag(), $value[0]->getTag());
    }

    public function testMultipleValues()
    {
        $def = new ImagesDefinition();
        $def->setValue(array('namespace/name:tag', 'namespace1/name', 'ubuntu'));

        $value = $def->getValue();
        $this->assertTrue(is_array($value));
        $this->assertCount(3, $value);
        $this->assertInstanceOf('Frizinak\\Corked\\Definition\\FromDefinition', $value[0]);
        $this->assertInstanceOf('Frizinak\\Corked\\Definition\\FromDefinition', $value[1]);
        $this->assertInstanceOf('Frizinak\\Corked\\Definition\\FromDefinition', $value[2]);
    }
}
