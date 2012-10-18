<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\SerializerBundle\Serializer\Handler\SubscribingHandlerInterface;
use JMS\SerializerBundle\Serializer\GenericSerializationVisitor;
use JMS\SerializerBundle\Serializer\GraphNavigator;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;

use FSC\HateoasBundle\Model\LinksAwareWrapper;
use FSC\HateoasBundle\Serializer\LinkSerializationHelper;

class LinksAwareWrapperHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => 'FSC\HateoasBundle\Model\LinksAwareWrapper',
                'method' => 'serializeTo'.('xml' == $format ? 'XML' : 'Array'),
            );
        }

        return $methods;
    }

    protected $linkSerializationHelper;

    public function __construct(LinkSerializationHelper $linkSerializationHelper)
    {
        $this->linkSerializationHelper = $linkSerializationHelper;
    }

    public function serializeToXML(XmlSerializationVisitor $visitor, LinksAwareWrapper $linksAwareWrapper, array $type)
    {
        if (null === $visitor->document) {
            $visitor->document = $visitor->createDocument(null, null, true);
        }

        $this->linkSerializationHelper->addLinksToXMLSerialization($linksAwareWrapper->getLinks(), $visitor);

        return $visitor->getNavigator()->accept($linksAwareWrapper->getData(), null, $visitor);
    }

    public function serializeToArray(GenericSerializationVisitor $visitor, LinksAwareWrapper $linksAwareWrapper, array $type)
    {
        $data = $visitor->getNavigator()->accept($linksAwareWrapper->getData(), null, $visitor);

        $links = $this->linkSerializationHelper->createGenericLinksData($linksAwareWrapper->getLinks(), $visitor);
        $data['links'] = isset($data['links']) ? array_merge($data['links'], $links) : $links;

        return $data;
    }
}
