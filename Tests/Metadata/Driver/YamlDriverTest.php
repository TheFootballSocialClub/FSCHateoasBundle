<?php

namespace FSC\HateoasBundle\Tests\Metadata\Driver;

use Metadata\Driver\FileLocator;

use FSC\HateoasBundle\Metadata\Driver\YamlDriver;

class YamlDriverTest extends \PHPUnit_Framework_TestCase
{
    protected function createDriver()
    {
        return new YamlDriver(new FileLocator(array(
            'FSC\HateoasBundle\Tests\Fixtures' => __DIR__.'/yml',
        )));
    }

    public function testUser()
    {
        $driver = $this->createDriver();
        $classMetadata = $driver->loadMetadataForClass(new \ReflectionClass('FSC\HateoasBundle\Tests\Fixtures\User'));

        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\ClassMetadata', $classMetadata);

        $this->assertEquals(array(
            array(
                'rel' => 'self',
                'route' => '_some_route',
                'params' => array('identifier' => 'id')
            ),
            array(
                'rel' => 'users',
                'route' => '_users',
                'params' => array(),
            ),
            array(
                'rel' => 'home',
                'route' => 'homepage',
                'params' => array(),
            ),
        ), $classMetadata->getLinks());
    }
}