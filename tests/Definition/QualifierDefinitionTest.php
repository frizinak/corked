<?php
namespace Frizinak\CorkedTest\Definition;

use Frizinak\Corked\Definition\FromDefinition;

class QualifierDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function validQualifierProvider()
    {
        return $this->getCsvArray('valid_qualifiers');
    }

    protected function getCsvArray($name)
    {
        $path = CORKED_TEST_RESOURCES_ROOT . DIRECTORY_SEPARATOR . $name . '.csv';
        if (!($fh = fopen($path, 'r'))) {
            throw new \RuntimeException(sprintf('Could not fopen csv file at %s', $path));
        }

        $data = array();
        while ($row = fgetcsv($fh)) {
            $data[] = $row;
        }
        return $data;
    }

    public function invalidQualifierProvider()
    {
        return $this->getCsvArray('invalid_qualifiers');
    }

    public function multipleValidQualifierProvider()
    {
        return $this->multipleProvider('valid_qualifiers');
    }

    protected function multipleProvider($which)
    {
        $data = array();
        $arr = $this->getCsvArray($which);
        foreach ($arr as $row) {
            foreach ($row as $k => $item) {
                $data[$k][] = $item;
            }
        }
        return array($data);
    }

    public function multipleinValidQualifierProvider()
    {
        return $this->multipleProvider('invalid_qualifiers');
    }

    /**
     * @dataProvider validQualifierProvider
     */
    public function testValidQualifier($qualifier, $namespace, $name, $tag)
    {
        $definition = new FromDefinition();
        $definition->setValue($qualifier);
        $parsed = $definition->getValue();
        $this->assertCount(3, $parsed);
        foreach (array($namespace, $name, $tag) as $i => $item) {
            $this->assertEquals($item, $parsed[$i]);
        }

        $this->assertEquals($namespace, $definition->getNamespace());
        $this->assertEquals($name, $definition->getName());
        $this->assertEquals($tag, $definition->getTag());
        $repo = empty($namespace) ? $name : sprintf('%s/%s', $namespace, $name);
        $fullQualifier = empty($namespace) ? sprintf('%s:%s', $name, $tag) : sprintf('%s/%s:%s', $namespace, $name, $tag);
        $this->assertEquals($repo, $definition->getRepository());
        $this->assertEquals($fullQualifier, $definition->getFullQualifier());
    }

    /**
     * @dataProvider invalidQualifierProvider
     * @expectedException \Frizinak\Corked\Definition\Exception\QualifierValidationException
     */
    public function testInvalidQualifier($qualifier, $msgCode)
    {
        $definition = new FromDefinition();
        try {
            $definition->setValue($qualifier);
        } catch (\Exception $e) {
            $this->assertEquals($msgCode, $e->getCode());
            throw $e;
        }
    }

    public function testInvalidTypeQualifier()
    {
        $definition = new FromDefinition();
        $qualifiers = array(
            'array' => array(),
            'multiple integers' => array(1, 2),
            'integer' => 1,
            'object' => new \stdClass()
        );
        foreach ($qualifiers as $type => $qualifier) {
            try {
                $definition->setValue($qualifier);
            } catch (\Exception $e) {
                $this->assertInstanceOf('Frizinak\\Corked\\Definition\\Exception\\QualifierValidationException', $e);
                continue;
            }
            $this->fail(sprintf('QualifierValidationException was not thrown for invalid qualifier of type %s', $type));
        }
    }
}
