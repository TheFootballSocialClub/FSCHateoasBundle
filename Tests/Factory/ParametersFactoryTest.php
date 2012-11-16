<?php

namespace FSC\HateoasBundle\Tests\Util;

use FSC\HateoasBundle\Factory\ParametersFactory;

class ParametersFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestCreateParametersData
     */
    public function testCreateParameters($data, $parameters, $expectedResult)
    {
        $parametersFactory = new ParametersFactory();
        $this->assertEquals($expectedResult, $parametersFactory->createParameters($data, $parameters));
    }

    public function getTestCreateParametersData()
    {
        return array(
            array(
                array(
                    'uuid' => 23,
                ),
                array(
                    'id' => '[uuid]',
                ),
                array(
                    'id' => 23,
                ),
            ),
            array(
                array(
                    'id' => 23,
                    'friend' => array('id' => 4),
                ),
                array(
                    'id' => '[id]',
                    'friend_id' => '[friend][id]',
                ),
                array(
                    'id' => 23,
                    'friend_id' => 4,
                ),
            ),
            array(
                array(
                    'id' => 1,
                ),
                array(
                    'id' => '4',
                    'arguments' => array('1')
                ),
                array(
                    'id' => 4,
                    'arguments' => array('1')
                ),
            ),
        );
    }
}
