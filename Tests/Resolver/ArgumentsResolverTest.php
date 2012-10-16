<?php

namespace FSC\HateoasBundle\Tests\Resolver;

use FSC\HateoasBundle\Resolver\ArgumentsResolver;

class ArgumentsResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestResolveData
     */
    public function testResolve(array $methodArguments, array $parameters, array $expectedArguments)
    {
        $argumentsResolver = new ArgumentsResolver();
        $method = $this->createMethod($methodArguments);

        $this->assertEquals($expectedArguments, $argumentsResolver->resolve($method, $parameters));
    }

    public function getTestResolveData()
    {
        return array(
            array(
                array('name', 'id'),
                array('name' => 'adrien', 'id' => 3),
                array('adrien', 3),
            ),
            array(
                array('id', 'name'),
                array('name' => 'adrien', 'id' => 3),
                array(3, 'adrien'),
            ),
            array(
                array('id'),
                array('name' => 'adrien', 'id' => 3),
                array(3),
            ),
            array(
                array('name'),
                array('name' => 'adrien', 'id' => 3),
                array('adrien'),
            ),
        );
    }

    protected function createMethod(array $argumentsNames)
    {
        $parameters = array();
        foreach ($argumentsNames as $argumentsName) {
            $parameter = $this->getMock('\ReflectionParameter', array(), array(), '', false);
            $parameter
                ->expects($this->once())
                ->method('getName')
                ->will($this->returnValue($argumentsName));

            $parameters[] = $parameter;
        }

        $method = $this->getMock('\ReflectionFunctionAbstract', array(), array(), '', false);
        $method
            ->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue($parameters))
        ;

        return $method;
    }
}
