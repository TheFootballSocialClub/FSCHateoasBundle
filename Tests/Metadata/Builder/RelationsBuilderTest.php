<?php

namespace FSC\Tests\Metadata\Builder;

use FSC\HateoasBundle\Metadata\Builder\RelationsBuilder;

class RelationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $RelationsBuilder = new RelationsBuilder();

        $relationsMetadata = $RelationsBuilder->build();

        $this->assertInternalType('array', $relationsMetadata);
    }

    public function testAddSimpleRouteRelation()
    {
        $RelationsBuilder = new RelationsBuilder();

        $RelationsBuilder->add('self', array('route' => $route = '_some_route'));

        $relationsMetadata = $RelationsBuilder->build();

        $this->assertInternalType('array', $relationsMetadata);

        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationMetadataInterface', $relationsMetadata[0]);
        $this->assertEquals($route, $relationsMetadata[0]->getRoute());
    }

    public function testAdd2SimpleRouteRelation()
    {
        $RelationsBuilder = new RelationsBuilder();

        $RelationsBuilder->add('self', array('route' => $route = '_some_route'));
        $RelationsBuilder->add('self', array('route' => $route2 = '_some_route2'));

        $relationsMetadata = $RelationsBuilder->build();

        $this->assertInternalType('array', $relationsMetadata);

        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationMetadataInterface', $relationsMetadata[0]);
        $this->assertEquals($route, $relationsMetadata[0]->getRoute());
        $this->assertEquals($route2, $relationsMetadata[1]->getRoute());
    }

    public function testAddSimpleRouteRelationWithParams()
    {
        $RelationsBuilder = new RelationsBuilder();

        $RelationsBuilder->add('self', array(
            'route' => $route = '_some_route',
            'parameters' => $params = array('id' => 1),
        ));

        $relationsMetadata = $RelationsBuilder->build();

        $this->assertInternalType('array', $relationsMetadata);

        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationMetadataInterface', $relationsMetadata[0]);
        $this->assertEquals($route, $relationsMetadata[0]->getRoute());
        $this->assertEquals($params, $relationsMetadata[0]->getParams());
    }

    public function testAddEmbeddedRelation()
    {
        $RelationsBuilder = new RelationsBuilder();

        $RelationsBuilder->add('self',
            array(
                'route' => '_some_route',
            ), array(
                'provider' => array($serviceId = 'acme.foo.provider', $method = 'getUsers'),
                'providerArguments' => $arguments = array('1', 3, 'hello'),
                'serializerType' => 'array<Foo>',
                'serializerXmlElementName' => $xmlName = 'users',
                'serializerXmlElementRootMetadata' => $xmlRootMetadata = true,
            )
        );

        $relationsMetadata = $RelationsBuilder->build();

        $this->assertInternalType('array', $relationsMetadata);

        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationContentMetadataInterface', $relationsMetadata[0]->getContent());
        $this->assertEquals($serviceId, $relationsMetadata[0]->getContent()->getProviderId());
        $this->assertEquals($method, $relationsMetadata[0]->getContent()->getProviderMethod());
        $this->assertEquals($arguments, $relationsMetadata[0]->getContent()->getProviderArguments());
        $this->assertEquals($xmlName, $relationsMetadata[0]->getContent()->getSerializerXmlElementName());
        $this->assertEquals($xmlRootMetadata, $relationsMetadata[0]->getContent()->getSerializerXmlElementRootName());
    }
}
