<?php

namespace FSC\HateoasBundle\Tests\Factory;

use FSC\HateoasBundle\Factory\LinkFactory;

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
        $metadataFactory = $this->getMock('Metadata\MetadataFactoryInterface');
        $parametersFactory = new \FSC\HateoasBundle\Factory\ParametersFactory();
        $linkFactory = new LinkFactory($urlGenerator, $metadataFactory, $parametersFactory);

        $object = (object) array('id' => $id = 3);

        $relationMetadata = $this->getMock('FSC\HateoasBundle\Metadata\RelationMetadataInterface');
        $relationMetadata->expects($this->any())->method('getRel')->will($this->returnValue($rel = 'self'));
        $relationMetadata->expects($this->any())->method('getRoute')->will($this->returnValue($route = 'bar'));
        $relationMetadata->expects($this->any())->method('getParams')->will($this->returnValue(array('identifier' => 'id')));

        $classMetadata = $this->getMock('FSC\HateoasBundle\Metadata\ClassMetadataInterface');
        $classMetadata->expects($this->once())->method('getRelations')->will($this->returnValue(array($relationMetadata)));

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($route, array('identifier' => $id), true)
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
}
