<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\SerializerBundle\Serializer\Handler\SubscribingHandlerInterface;
use JMS\SerializerBundle\Serializer\GraphNavigator;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Serializer\GenericSerializationVisitor;
use Pagerfanta\Pagerfanta;

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

    public function serializeToXML(XmlSerializationVisitor $visitor, Pagerfanta $pager, array $resultsType)
    {
        $currentNode = $visitor->getCurrentNode(); /** @var $currentNode \DOMElement */
        $currentNode->setAttribute('page', $pager->getCurrentPage());
        $currentNode->setAttribute('limit', $pager->getMaxPerPage());
        $currentNode->setAttribute('total', $pager->getNbResults());

        $resultsType = isset($resultsType['params'][0]) ? $resultsType['params'][0] : null;

        return $visitor->getNavigator()->accept($pager->getCurrentPageResults(), $resultsType, $visitor);
    }

    public function serializeToArray(GenericSerializationVisitor $visitor, Pagerfanta $pager, array $type)
    {
        $resultsType = isset($type['params'][0]) ? $type['params'][0] : null;

        $data = array(
            'page' => $pager->getCurrentPage(),
            'limit' => $pager->getMaxPerPage(),
            'total' => $pager->getNbResults(),
            'results' => $visitor->getNavigator()->accept($pager->getCurrentPageResults(), $resultsType, $visitor),
        );

        return $data;
    }
}
