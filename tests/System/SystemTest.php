<?php


namespace Frizinak\CorkedTest\System;

use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\Corked;

/**
 * @group system
 */
class SystemTest extends \PHPUnit_Framework_TestCase
{

    public function testSystem()
    {
        $args = array('lookup_paths' => array(CORKED_TEST_RESOURCES_ROOT . DIRECTORY_SEPARATOR . 'corked'));
        $corked = new Corked($args);

        $root = $corked->getFactory()->createRoot(array(
            'images' => array('frizinak/name-file:tag-file'),
        ));

        $dependents = $root->getDependents();
        $this->assertCount(1, $dependents);
        /** @var Cork $cork */
        $cork = reset($dependents);
        $this->assertEquals(array("RUN python-software-properties-lala"), $cork->getInstructions());
    }
}
