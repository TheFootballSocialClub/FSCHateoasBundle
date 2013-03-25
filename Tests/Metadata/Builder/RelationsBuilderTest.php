<?php

namespace FSC\HateoasBundle\Tests\Metadata\Builder;

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

    public function testAddEmbeddedRelationProperty()
    {
        $RelationsMetadataBuilder = new RelationsBuilder();

        $RelationsMetadataBuilder->add('self',
            array(
                'route' => '_some_route',
            ), array(
                'property' => '.someProperty',
                'serializerType' => 'array<Foo>',
                'serializerXmlElementName' => $xmlName = 'users',
                'serializerXmlElementRootMetadata' => $xmlRootMetadata = true,
            )
        );

        $relationsMetadata = $RelationsMetadataBuilder->build();
        $this->assertInternalType('array', $relationsMetadata);

        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationContentMetadataInterface', $relationsMetadata[0]->getContent());
        $this->assertEquals('fsc_hateoas.factory.identity', $relationsMetadata[0]->getContent()->getProviderId());
        $this->assertEquals('get', $relationsMetadata[0]->getContent()->getProviderMethod());
        $this->assertEquals(array('.someProperty'), $relationsMetadata[0]->getContent()->getProviderArguments());
        $this->assertEquals($xmlName, $relationsMetadata[0]->getContent()->getSerializerXmlElementName());
        $this->assertEquals($xmlRootMetadata, $relationsMetadata[0]->getContent()->getSerializerXmlElementRootName());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Content configuration can only have either a provider or a property.
     */
    public function testFail1()
    {
        $relationsMetadataBuilder = new RelationsBuilder();

        $relationsMetadataBuilder->add('foo', array('route' => 'foo', ), array(
            'property' => '.someProperty',
            'provider' => 'lala',
        ));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Content configuration needs either a provider or a property.
     */
    public function testFail2()
    {
        $relationsMetadataBuilder = new RelationsBuilder();

        $relationsMetadataBuilder->add('foo', array('route' => 'foo', ), array(
            'a' => '.someProperty',
            'b' => 'lala',
        ));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Content configuration needs either a provider or a property.
     */
    public function testFail3()
    {
        $relationsMetadataBuilder = new RelationsBuilder();

        $relationsMetadataBuilder->add('foo', array('route' => 'foo', ), array(

        ));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Content "provider" is required, and should be an array of 2 values. [service, method]
     */
    public function testFail4()
    {
        $relationsMetadataBuilder = new RelationsBuilder();

        $relationsMetadataBuilder->add('foo', array('route' => 'foo', ), array(
            'provider' => array('a'),
        ));
    }

    public function testClear()
    {
        $RelationsBuilder = new RelationsBuilder();

        $RelationsBuilder->add('self', array('route' => $route = '_some_route'));

        $relationsMetadata = $RelationsBuilder->build();

        $this->assertInternalType('array', $relationsMetadata);
        $this->assertCount(1, $relationsMetadata);

        $relationsMetadata = $RelationsBuilder->clear();
        $relationsMetadata = $RelationsBuilder->build();

        $this->assertInternalType('array', $relationsMetadata);
        $this->assertCount(0, $relationsMetadata);
    }

    public function testAddRouteWithOptions()
    {
        $RelationsBuilder = new RelationsBuilder();

        $RelationsBuilder->add('self', array('route' => $route = '_some_route', 'options' => $options = array('value1' => '123')));

        $relationsMetadata = $RelationsBuilder->build();

        $this->assertInternalType('array', $relationsMetadata);

        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationMetadataInterface', $relationsMetadata[0]);
        $this->assertEquals($route, $relationsMetadata[0]->getRoute());
        $this->assertEquals($options, $relationsMetadata[0]->getOptions());
    }

    public function testAddRouteTemplated()
    {
        $RelationsBuilder = new RelationsBuilder();

        $RelationsBuilder->add('self', array('route' => $route = '_some_route'), null, true);

        $relationsMetadata = $RelationsBuilder->build();

        $this->assertInternalType('array', $relationsMetadata);

        $this->assertInstanceOf('FSC\HateoasBundle\Metadata\RelationMetadataInterface', $relationsMetadata[0]);
        $this->assertEquals($route, $relationsMetadata[0]->getRoute());
        $this->assertTrue($relationsMetadata[0]->getTemplated());
    }
}
