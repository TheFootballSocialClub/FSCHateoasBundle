<?php

namespace FSC\HateoasBundle\Tests\Metadata\Driver;

use Metadata\Driver\FileLocator;
use Doctrine\Common\Annotations\AnnotationReader;

use FSC\HateoasBundle\Metadata\Driver\AnnotationDriver;
use FSC\HateoasBundle\Metadata\Driver\YamlDriver;

class CommonDriverTest extends \PHPUnit_Framework_TestCase
{
    protected function createAnnotationDriver()
    {
        return new AnnotationDriver(new AnnotationReader());
    }

    protected function createYamlDriver()
    {
        return new YamlDriver(new FileLocator(array(
            'FSC\HateoasBundle\Tests\Fixtures' => __DIR__.'/yml',
        )));
    }

    public function getDrivers()
    {
        return array(
            array($this->createAnnotationDriver()),
            array($this->createYamlDriver()),
        );
    }

    /**
     * @dataProvider getDrivers
     */
    public function testUser($driver)
    {
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