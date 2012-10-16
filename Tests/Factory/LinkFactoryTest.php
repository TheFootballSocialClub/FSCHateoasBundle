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
        $linkFactory = new LinkFactory($urlGenerator, $metadataFactory);

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

    /**
     * @dataProvider getTestCreateRouteParametersData
     */
    public function testCreateRouteParameters($data, $params, $expectedRouteParams)
    {
        $this->assertEquals($expectedRouteParams, LinkFactory::createRouteParameters($params, $data));
    }

    public function getTestCreateRouteParametersData()
    {
        return array(
            array(
                array(
                    'uuid' => 23,
                ),
                array(
                    'id' => '[uuid]',
                ),
                array(
                    'id' => 23,
                ),
            ),
            array(
                array(
                    'id' => 23,
                    'friend' => array('id' => 4),
                ),
                array(
                    'id' => '[id]',
                    'friend_id' => '[friend][id]',
                ),
                array(
                    'id' => 23,
                    'friend_id' => 4,
                ),
            ),
        );
    }
}
