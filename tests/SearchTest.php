<?php

namespace Stackoverflow\Test;


use \Stackoverflow\Search;

class SearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTestSearchWithRequiredOptionsSucceeds
     */
    public function testRequests($opts)
    {
        $search = new Search($opts);
        $search->run();

        $this->assertEquals(200, $search->getResponse()->getStatusCode());
    }

    public function testBadRequest()
    {
        $search = new Search(array());
        $search->run();

        $this->assertEquals(400, $search->getResponse()->getStatusCode());
    }

    /**
     * @expectedException \Stackoverflow\Exception\StackoverflowException
     */
    public function testSearchThrowsException()
    {
        $search = new Search(array('invalid'=>'option'));
        $search->run();
    }

    /**
     * Data ProviproviderTestSearchWithRequiredOptionsSucceeds()
     */
    public function providerTestSearchWithRequiredOptionsSucceeds()
    {
        return array(
            array(
                array(
                    'intitle' => 'phpunit',
                ),
            ),
            array(
                array(
                    'tagged' => 'php',
                ),
            ),
            array(
                array(
                    'intitle' => 'phpunit',
                ),
                array(
                    'tagged' => 'php',
                ),
            ),
        );
    }

}
