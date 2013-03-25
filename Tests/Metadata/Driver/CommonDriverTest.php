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

        $relationsMetadata = $classMetadata->getRelations();
        foreach ($relationsMetadata as $relationMetadata) {
            $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationMetadataInterface', $relationMetadata);
        }

        $n = 0;
        $relationMetadata = null; /** @var $relationMetadata \FSC\HateoasBundle\Metadata\RelationMetadataInterface */

        $relationMetadata = $relationsMetadata[$n];
        $this->assertEquals('self', $relationMetadata->getRel());
        $this->assertEquals('_some_route', $relationMetadata->getRoute());
        $this->assertEquals(array('identifier' => 'id'), $relationMetadata->getParams());
        $this->assertNull($relationMetadata->getContent());

        $n++;

        $relationMetadata = $relationsMetadata[$n];
        $this->assertEquals('alternate', $relationMetadata->getRel());
        $this->assertEquals('_some_route2', $relationMetadata->getRoute());
        $this->assertEquals(array(), $relationMetadata->getParams());
        $this->assertNull($relationMetadata->getContent());

        $n++;

        $relationMetadata = $relationsMetadata[$n];
        $this->assertEquals('alternate', $relationMetadata->getRel());
        $this->assertEquals('_some_route3', $relationMetadata->getRoute());
        $this->assertEquals(array(), $relationMetadata->getParams());
        $this->assertNull($relationMetadata->getContent());

        $n++;

        $relationMetadata = $relationsMetadata[$n];
        $this->assertEquals('home', $relationMetadata->getRel());
        $this->assertEquals('homepage', $relationMetadata->getRoute());
        $this->assertEquals(array(), $relationMetadata->getParams());
        $this->assertNull($relationMetadata->getContent());
        $this->assertFalse($relationMetadata->getTemplated());

        $n++;

        $relationMetadata = $relationsMetadata[$n];
        $this->assertEquals('friends', $relationMetadata->getRel());
        $this->assertEquals('user_friends_list', $relationMetadata->getRoute());
        $this->assertEquals(array('id' => 'id'), $relationMetadata->getParams());
        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationContentMetadataInterface', $relationMetadata->getContent());
        $this->assertEquals('acme.foo.user_provider', $relationMetadata->getContent()->getProviderId());
        $this->assertEquals('getUserFriendsPager', $relationMetadata->getContent()->getProviderMethod());
        $this->assertNull($relationMetadata->getContent()->getSerializerType());
        $this->assertNull($relationMetadata->getContent()->getSerializerXmlElementName());
        $this->assertTrue($relationMetadata->getContent()->getSerializerXmlElementRootName());

        $n++;

        $relationMetadata = $relationsMetadata[$n];
        $this->assertEquals('favorites', $relationMetadata->getRel());
        $this->assertEquals('user_favorites_list', $relationMetadata->getRoute());
        $this->assertEquals(array('id' => 'id'), $relationMetadata->getParams());
        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationContentMetadataInterface', $relationMetadata->getContent());
        $this->assertEquals('acme.foo.favorite_provider', $relationMetadata->getContent()->getProviderId());
        $this->assertEquals('getUserFavoritesPager', $relationMetadata->getContent()->getProviderMethod());
        $this->assertEquals(array('id', '=3'), $relationMetadata->getContent()->getProviderArguments());
        $this->assertEquals('Pagerfanta<custom>', $relationMetadata->getContent()->getSerializerType());
        $this->assertEquals('favorites', $relationMetadata->getContent()->getSerializerXmlElementName());
        $this->assertTrue($relationMetadata->getContent()->getSerializerXmlElementRootName());

        $n++;

        $relationMetadata = $relationsMetadata[$n];
        $this->assertEquals('disclosure', $relationMetadata->getRel());
        $this->assertEquals('homepage', $relationMetadata->getRoute());
        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationContentMetadataInterface', $relationMetadata->getContent());
        $this->assertEquals('fsc_hateoas.factory.identity', $relationMetadata->getContent()->getProviderId());
        $this->assertEquals('get', $relationMetadata->getContent()->getProviderMethod());
        $this->assertEquals(array('.property'), $relationMetadata->getContent()->getProviderArguments());

        $n++;

        $relationMetadata = $relationsMetadata[$n];
        $this->assertEquals('adrienbrault', $relationMetadata->getRel());
        $this->assertEquals('http://adrienbrault.fr', $relationMetadata->getUrl());
        $this->assertNull($relationMetadata->getRoute());
        $this->assertNull($relationMetadata->getContent());

        $n++;

        $relationMetadata = $relationsMetadata[$n];
        $this->assertEquals('options', $relationMetadata->getRel());
        $this->assertEquals('homepage', $relationMetadata->getRoute());

        $optionsArray = array(
            'key1' => 'value1'
        );
        $this->assertEquals($optionsArray, $relationMetadata->getOptions());

        $n++;

        $relationMetadata = $relationsMetadata[$n];
        $this->assertEquals('templated', $relationMetadata->getRel());
        $this->assertEquals('homepage', $relationMetadata->getRoute());
        $this->assertEquals(array(), $relationMetadata->getParams());
        $this->assertTrue($relationMetadata->getTemplated());
    }
}
