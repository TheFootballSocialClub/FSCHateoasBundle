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

        $classMetadata = $this->getMock('FSC\HateoasBundle\Metadata\ClassMetadataInterface');
        $classMetadata
            ->expects($this->once())
            ->method('getRelations')
            ->will($this->returnValue($metadataLinks = array(
                array(
                    'rel' => $rel = 'self',
                    'route' => $route = 'bar',
                    'params' => array('identifier' => 'id')
                ),
            )))
        ;

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
