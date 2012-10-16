<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\SerializerBundle\Serializer\Handler\SubscribingHandlerInterface;
use JMS\SerializerBundle\Serializer\GraphNavigator;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Serializer\GenericSerializationVisitor;
use Pagerfanta\Pagerfanta;

use FSC\HateoasBundle\Serializer\EventSubscriber\LinkEventSubscriber;

class PagerfantaHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => 'Pagerfanta\Pagerfanta',
                'method' => 'serializeTo'.('xml' == $format ? 'XML' : 'Array'),
            );
        }

        return $methods;
    }

    protected $linkEventSubscriber;

    public function __construct(LinkEventSubscriber $linkEventSubscriber)
    {
        $this->linkEventSubscriber = $linkEventSubscriber;
    }

    public function serializeToXML(XmlSerializationVisitor $visitor, Pagerfanta $pager, array $resultsType)
    {
        $currentNode = $visitor->getCurrentNode(); /** @var $currentNode \DOMElement */
        $currentNode->setAttribute('page', $pager->getCurrentPage());
        $currentNode->setAttribute('limit', $pager->getMaxPerPage());
        $currentNode->setAttribute('total', $pager->getNbResults());

        $link = new \FSC\HateoasBundle\Model\Link();
        $link->setRel('next');
        $link->setHref('http://hohoho');
        $links = array($link); // TODO
        $this->linkEventSubscriber->addLinksToXMLSerialization($links, $visitor);

        $resultsType = isset($resultsType['params'][0]) ? $resultsType['params'][0] : null;
        return $visitor->getNavigator()->accept($pager->getCurrentPageResults(), $resultsType, $visitor);
    }

    public function serializeToArray(GenericSerializationVisitor $visitor, Pagerfanta $pager, array $type)
    {
        $resultsType = isset($type['params'][0]) ? $type['params'][0] : null;

        $link = new \FSC\HateoasBundle\Model\Link();
        $link->setRel('next');
        $link->setHref('http://hohoho');
        $links = array($link); // TODO

        $data = array(
            'page' => $pager->getCurrentPage(),
            'limit' => $pager->getMaxPerPage(),
            'total' => $pager->getNbResults(),
            'results' => $visitor->getNavigator()->accept($pager->getCurrentPageResults(), $resultsType, $visitor),
            'links' => $this->linkEventSubscriber->createGenericLinksData($links, $visitor),
        );

        return $data;
    }
}
