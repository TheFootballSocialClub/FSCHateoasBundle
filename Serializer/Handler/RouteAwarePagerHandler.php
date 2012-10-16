<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\SerializerBundle\Serializer\Handler\SubscribingHandlerInterface;
use JMS\SerializerBundle\Serializer\GraphNavigator;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Serializer\GenericSerializationVisitor;

use FSC\HateoasBundle\Model\RouteAwarePager;
use FSC\HateoasBundle\Factory\PagerLinkFactoryInterface;
use FSC\HateoasBundle\Serializer\LinkSerializationHelper;

class RouteAwarePagerHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => 'FSC\HateoasBundle\Model\RouteAwarePager',
                'method' => 'serializeTo'.('xml' == $format ? 'XML' : 'Array'),
            );
        }

        return $methods;
    }

    protected $pagerLinkFactory;
    protected $linkSerializationHelper;

    public function __construct(PagerLinkFactoryInterface $pagerLinkFactory, LinkSerializationHelper $linkSerializationHelper)
    {
        $this->pagerLinkFactory = $pagerLinkFactory;
        $this->linkSerializationHelper = $linkSerializationHelper;
    }

    public function serializeToXML(XmlSerializationVisitor $visitor, RouteAwarePager $routeAwarePager, array $type)
    {
        $links = $this->pagerLinkFactory->createPagerLinks($routeAwarePager->getPager(), $routeAwarePager->getRoute(), $routeAwarePager->getRouteParameters());
        $this->linkSerializationHelper->addLinksToXMLSerialization($links, $visitor);

        return $visitor->getNavigator()->accept($routeAwarePager->getPager(), $this->getResultsType($type), $visitor);
    }

    public function serializeToArray(GenericSerializationVisitor $visitor, RouteAwarePager $routeAwarePager, array $type)
    {
        $data = $visitor->getNavigator()->accept($routeAwarePager->getPager(), $this->getResultsType($type), $visitor);

        $links = $this->pagerLinkFactory->createPagerLinks($routeAwarePager->getPager(), $routeAwarePager->getRoute(), $routeAwarePager->getRouteParameters());
        $data['links'] = $this->linkSerializationHelper->createGenericLinksData($links, $visitor);

        return $data;
    }

    public function getResultsType($type)
    {
        return isset($type['params'][0]) ? $type['params'][0] : null;
    }
}
