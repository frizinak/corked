<?php


namespace Frizinak\CorkedTest\DependencyResolver;

use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\Decoder\JsonDecoder;
use Frizinak\Corked\DependencyResolver\FileResolver;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{

    protected function getResolver()
    {
        $decoder = new JsonDecoder();
        $resolver = new FileResolver();
        $resolver->addDecoder('corked.json', $decoder);
        return $resolver;
    }

    /**
     * @expectedException \Frizinak\Corked\DependencyResolver\Exception\DependencyResolvingFailedException
     * @expectedExceptionMessageRegExp /empty namespace/
     */
    public function testEmptyNamespace()
    {
        $cork = new Cork();
        $cork->getDefinition('from')->setValue('ubuntu:14.04');
        $resolver = $this->getResolver();
        $resolver->resolve($cork);
    }

    /**
     * @expectedException \Frizinak\Corked\DependencyResolver\Exception\DependencyResolvingFailedException
     */
    public function testNotFound()
    {
        $cork = new Cork();
        $cork->getDefinition('from')->setValue('frizinak/doesnt-exist:latest');
        $resolver = $this->getResolver();
        $resolver->addLookupPath(CORKED_TEST_RESOURCES_ROOT . DIRECTORY_SEPARATOR . 'corked');
        $resolver->resolve($cork);
    }

    /**
     * @expectedException \Frizinak\Corked\Decoder\Exception\DecodingFailedException
     */
    public function testInvalidData()
    {
        $cork = new Cork();
        $cork->getDefinition('from')->setValue('frizinak/invalid:file');

        $resolver = $this->getResolver();
        $resolver->addLookupPath(CORKED_TEST_RESOURCES_ROOT . DIRECTORY_SEPARATOR . 'corked');
        $resolver->resolve($cork);
    }

    /**
     * @expectedException \Frizinak\Corked\DependencyResolver\Exception\InvalidDataException
     */
    public function testInvalidDataType()
    {
        $cork = new Cork();
        $cork->getDefinition('from')->setValue('frizinak/valid-json:invalid-data-type');

        $resolver = $this->getResolver();
        $resolver->addLookupPath(CORKED_TEST_RESOURCES_ROOT . DIRECTORY_SEPARATOR . 'corked');
        $resolver->resolve($cork);
    }

    public function testValidData()
    {
        $cork = new Cork();
        $cork->getDefinition('from')->setValue('frizinak/name-dir:tag-file');

        $resolver = $this->getResolver();
        $resolver->addLookupPath(CORKED_TEST_RESOURCES_ROOT . DIRECTORY_SEPARATOR . 'corked');

        $value = $resolver->resolve($cork);

        $this->assertTrue(is_array($value));
        $this->assertArrayHasKey('from', $value);
        $this->assertArrayHasKey('instructions', $value);
        $this->assertArrayHasKey('path', $value);
        $this->assertTrue(is_dir($value['path']));
    }

    public function testResolveNothingToResolver()
    {
        $cork = new Cork();
        $resolver = $this->getResolver();
        $resolver->addLookupPath(CORKED_TEST_RESOURCES_ROOT . DIRECTORY_SEPARATOR . 'corked');

        $this->assertNull($resolver->resolve($cork));
    }
}
