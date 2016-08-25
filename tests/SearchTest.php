<?php

namespace Stackoverflow\Tests;


use \Stackoverflow\Search;

class SearchTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider providerTestSearchWithRequiredOptionsSucceeds
     */
    public function testSearchWithRequiredOptionsSucceeds($opts)
    {
        $search = new Search($opts);
        $search->run();

        $this->assertEquals(200, $search->getResponse()->getStatusCode());
    }

    /**
     * @expectedException \Stackoverflow\Exception\StackoverflowException
     */
    public function testSearchThrowsExceptionMissingRequiredOptions()
    {
        $search = new Search(array());
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
