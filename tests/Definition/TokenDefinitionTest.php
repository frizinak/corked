<?php

namespace Frizinak\CorkedTest\Definition;

use Frizinak\Corked\Definition\TokensDefinition;

class TokenDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function validTokenProvider()
    {
        $data = array();
        $data[] = array(

            '--tokens--' => array(
                'ancestor' => array(
                    'grandfather' => array(
                        'mother' => array(
                            'child' => 'value',
                            'child2' => 1,
                            'child3' => array(
                                'grandchild' => true,
                            ),
                        ),
                    ),
                ),
            ),
            '--assertion--' => array(
                'ancestor.grandfather.mother.child' => 'value',
                'ancestor.grandfather.mother.child2' => 1,
                'ancestor.grandfather.mother.child3.grandchild' => true,
            )

        );

        $tokens = reset($data);
        $asObjects = json_decode(json_encode(reset($tokens)));
        $data[] = array($asObjects, end($tokens));

        return $data;
    }

    public function invalidTokenProvider()
    {
        $data = array();
        $data[] = array(1);
        $data[] = array(true);
        $data[] = array(null);
        $data[] = array(false);
        $data[] = array('string');

        return $data;
    }

    /**
     * @dataProvider validTokenProvider
     */
    public function testValidTokenDefinition($arr, $assert)
    {
        $tokenDefinition = new TokensDefinition();
        $tokenDefinition->setValue($arr);
        $result = $tokenDefinition->getValue();

        $this->assertCount(count($assert), $result);

        foreach ($assert as $token => $value) {
            $this->assertArrayHasKey($token, $result);
            $this->assertEquals($value, $result[$token]);
        }
    }

    /**
     * @dataProvider invalidTokenProvider
     * @expectedException \Frizinak\Corked\Definition\Exception\ValidationException
     */
    public function testInvalidTokenDefinition($item)
    {
        $tokenDefinition = new TokensDefinition();
        $tokenDefinition->setValue($item);
    }
}
