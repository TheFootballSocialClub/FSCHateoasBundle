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

    protected function createDriver($name)
    {
        switch ($name) {
            case 'annotation': return $this->createAnnotationDriver();
            case 'yaml': return $this->createYamlDriver();
        }

        throw new \RuntimeException(sprintf('Driver "%s" doesn\'t exists.', $name));
    }

    public function getDriversName()
    {
        return array(
            array('annotation'),
            array('yaml'),
        );
    }

    /**
     * @dataProvider getDriversName
     */
    public function testUser($driverName)
    {
        $driver = $this->createDriver($driverName);
        $classMetadata = $driver->loadMetadataForClass(new \ReflectionClass('FSC\HateoasBundle\Tests\Fixtures\User'));

        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\ClassMetadata', $classMetadata);

        $this->assertEquals(array(
            array(
                'rel' => 'self',
                'route' => '_some_route',
                'params' => array('identifier' => 'id')
            ),
            array(
                'rel' => 'alternate',
                'route' => '_some_route2',
                'params' => array(),
            ),
            array(
                'rel' => 'alternate',
                'route' => '_some_route3',
                'params' => array(),
            ),
            array(
                'rel' => 'home',
                'route' => 'homepage',
                'params' => array(),
            ),
            array(
                'rel' => 'friends',
                'route' => 'user_friends_list',
                'params' => array('id' => 'id'),
                'content_provider' => array('id' => 'acme.foo.user_provider', 'method' => 'getUserFriendsPager'),
            ),
        ), $classMetadata->getRelations());
    }
}
