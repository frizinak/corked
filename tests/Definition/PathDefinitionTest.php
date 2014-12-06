<?php


namespace Frizinak\CorkedTest\Definition;

use Frizinak\Corked\Definition\PathDefinition;

class PathDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function testValidPath()
    {
        $rel = str_replace(getcwd(), '', __DIR__);
        $rel = ltrim($rel, '/\//');

        $def = new PathDefinition();
        if (($abs = realpath($rel)) === false) {
            throw new \RuntimeException('Failed to create valid relative path');
        }

        $def->setValue($rel);
        $this->assertEquals($abs, $def->getValue());

        $def->setValue($abs);

        $this->assertEquals($abs, $def->getValue());
    }

    /**
     * @expectedException \Frizinak\Corked\Exception\ExceptionInterface
     */
    public function testValidFilePath()
    {
        $def = new PathDefinition();
        $def->setValue(__FILE__);
    }

    /**
     * @expectedException \Frizinak\Corked\Exception\ExceptionInterface
     */
    public function testInValidPath()
    {
        $def = new PathDefinition();
        $def->setValue(__DIR__ . DIRECTORY_SEPARATOR . 'does-not-exist');
    }
}
