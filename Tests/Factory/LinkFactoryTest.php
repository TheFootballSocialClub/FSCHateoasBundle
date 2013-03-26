<?php

namespace FSC\HateoasBundle\Tests\Factory;

use FSC\HateoasBundle\Factory\LinkFactory;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class LinkFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateLink()
    {
        $link = LinkFactory::createLink($rel = 'self', $href = 'http://ohoho');
        $this->assertInstanceOf('FSC\HateoasBundle\Model\Link', $link);
        $this->assertEquals($rel, $link->getRel());
        $this->assertEquals($href, $link->getHref());
    }

    public function testCreateLinksFromMetadata()
    {
        $urlGenerator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $metadataFactory = $this->getMock('FSC\HateoasBundle\Metadata\MetadataFactoryInterface');
        $parametersFactory = new \FSC\HateoasBundle\Factory\ParametersFactory(new PropertyAccessor());

        $FSCUrlGenerator = new \FSC\HateoasBundle\Routing\UrlGenerator($urlGenerator);

        $relationUrlGenerator = new \FSC\HateoasBundle\Routing\RelationUrlGenerator($metadataFactory, $parametersFactory);
        $relationUrlGenerator->setUrlGenerator('default', $FSCUrlGenerator);

        $linkFactory = new LinkFactory($metadataFactory, $parametersFactory, $relationUrlGenerator);

        $object = (object) array('id' => $id = 3);

        $relationMetadata = $this->getMock('FSC\HateoasBundle\Metadata\RelationMetadataInterface');
        $relationMetadata->expects($this->any())->method('getRel')->will($this->returnValue($rel = 'self'));
        $relationMetadata->expects($this->any())->method('getRoute')->will($this->returnValue($route = 'bar'));
        $relationMetadata->expects($this->any())->method('getParams')->will($this->returnValue(array('identifier' => '.id')));

        $classMetadata = $this->getMock('FSC\HateoasBundle\Metadata\ClassMetadataInterface');
        $classMetadata->expects($this->once())->method('getRelations')->will($this->returnValue(array($relationMetadata)));

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($route, array('identifier' => $id))
            ->will($this->returnValue($href = 'http://foo.com'))
        ;

        $links = $linkFactory->createLinksFromMetadata($classMetadata, $object);
        $link = $links[0];

        $this->assertInternalType('array', $links);
        $this->assertEquals(1, count($links));

        $this->assertInstanceOf('FSC\HateoasBundle\Model\Link', $link);
        $this->assertEquals($rel, $link->getRel());
        $this->assertEquals($href, $link->getHref());
    }

    public function testCreateLinksWithSameRelFromMetadata()
    {
        $urlGenerator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $metadataFactory = $this->getMock('FSC\HateoasBundle\Metadata\MetadataFactoryInterface');
        $parametersFactory = new \FSC\HateoasBundle\Factory\ParametersFactory(new PropertyAccessor());

        $FSCUrlGenerator = new \FSC\HateoasBundle\Routing\UrlGenerator($urlGenerator);

        $relationUrlGenerator = new \FSC\HateoasBundle\Routing\RelationUrlGenerator($metadataFactory, $parametersFactory);
        $relationUrlGenerator->setUrlGenerator('default', $FSCUrlGenerator);

        $linkFactory = new LinkFactory($metadataFactory, $parametersFactory, $relationUrlGenerator);

        $object = (object) array('id' => $id = 3);

        $relationMetadata = $this->getMock('FSC\HateoasBundle\Metadata\RelationMetadataInterface');
        $relationMetadata->expects($this->any())->method('getRel')->will($this->returnValue($rel = 'self'));
        $relationMetadata->expects($this->any())->method('getRoute')->will($this->returnValue($route = 'bar'));
        $relationMetadata->expects($this->any())->method('getParams')->will($this->returnValue(array('identifier' => '.id')));

        $classMetadata = $this->getMock('FSC\HateoasBundle\Metadata\ClassMetadataInterface');
        $classMetadata->expects($this->once())->method('getRelations')->will($this->returnValue(array($relationMetadata, $relationMetadata)));

        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->with($route, array('identifier' => $id))
            ->will($this->returnValue($href = 'http://foo.com'))
        ;

        $links = $linkFactory->createLinksFromMetadata($classMetadata, $object);

        $link = $links[0];

        $this->assertInternalType('array', $links);
        $this->assertEquals(2, count($links));

        $this->assertInstanceOf('FSC\HateoasBundle\Model\Link', $link);
        $this->assertEquals($rel, $link->getRel());
        $this->assertEquals($href, $link->getHref());
    }

    public function testLinkAttributes()
    {
        $attributes = array('isTemplated' => true);
        $link = LinkFactory::createLink($rel = 'self', $href = 'http://ohoho', $attributes);
        $this->assertInstanceOf('FSC\HateoasBundle\Model\Link', $link);
        $this->assertEquals($rel, $link->getRel());
        $this->assertEquals($href, $link->getHref());
        $this->assertEquals($attributes, $link->getAttributes());
    }
}
