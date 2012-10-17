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
                array('name' => null, 'id' => null),
                array('name' => 'adrien', 'id' => 3),
                array('adrien', 3),
            ),
            array(
                array('id' => null, 'name' => null),
                array('name' => 'adrien', 'id' => 3),
                array(3, 'adrien'),
            ),
            array(
                array('id' => null),
                array('name' => 'adrien', 'id' => 3),
                array(3),
            ),
            array(
                array('name' => null),
                array('name' => 'adrien', 'id' => 3),
                array('adrien'),
            ),
            array(
                array('id' => null, 'name' => 'adrien'),
                array('id' => 3),
                array(3, 'adrien'),
            ),
        );
    }

    protected function createMethod(array $argumentsNames)
    {
        $parameters = array();
        foreach ($argumentsNames as $argumentsName => $defaultValue) {
            $parameter = $this->getMock('\ReflectionParameter', array(), array(), '', false);
            $parameter
                ->expects($this->once())
                ->method('getName')
                ->will($this->returnValue($argumentsName))
            ;
            $parameter
                ->expects($this->any())
                ->method('getDefaultValue')
                ->will($this->returnValue($defaultValue))
            ;

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
