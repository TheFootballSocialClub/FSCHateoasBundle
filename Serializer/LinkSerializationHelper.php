<?php

namespace FSC\HateoasBundle\Serializer;

use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\TypeParser;
use JMS\Serializer\GenericSerializationVisitor;

class LinkSerializationHelper
{
    protected $typeParser;

    public function __construct(TypeParser $typeParser = null)
    {
        $this->typeParser = $typeParser ?: new TypeParser();
    }

    public function addLinksToXMLSerialization(array $links, XmlSerializationVisitor $visitor)
    {
        foreach ($links as $link) {
            $entryNode = $visitor->getDocument()->createElement('link');
            $visitor->getCurrentNode()->appendChild($entryNode);

            $entryNode->setAttribute('rel', $link->getRel());
            $entryNode->setAttribute('href', $link->getHref());

            $attributes = $link->getAttributes();
            foreach ($attributes as $attributeName => $attributeValue) {
                $entryNode->setAttribute($attributeName, $attributeValue);
            }
        }
    }

    public function createGenericLinksData(array $links, GenericSerializationVisitor $visitor)
    {
        $serializedLinks = array();

        foreach ($links as $link) {
            $rel = $link->getRel();
            $link->setRel(null); // To avoid serialization

            $serializedLink = array();
            if (null !== $link->getRel()) {
                $serializedLink['rel'] = $link->getRel();
            }
            if (null !== $link->getHref()) {
                $serializedLink['href'] = $link->getHref();
            }

            $serializedLink = array_merge($serializedLink, $link->getAttributes());

            if (isset($serializedLinks[$rel])) {
                if (isset($serializedLinks[$rel]['href'])) {
                    $serializedLinks[$rel] = array($serializedLinks[$rel]);
                }

                $serializedLinks[$rel][] = $serializedLink;
            } else {
                $serializedLinks[$rel] = $serializedLink;
            }
        }

        return $serializedLinks;
    }
}
