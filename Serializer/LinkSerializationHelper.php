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
            $visitor->setCurrentNode($entryNode);

            if (null !== $node = $visitor->getNavigator()->accept($link, null, $visitor)) {
                $visitor->getCurrentNode()->appendChild($node);
            }

            $visitor->revertCurrentNode();
        }
    }

    public function createGenericLinksData(array $links, GenericSerializationVisitor $visitor)
    {
        $serializedLinks = array();

        foreach ($links as $link) {
            $rel = $link->getRel();
            $link->setRel(null); // To avoid serialization

            $serializedLink = $visitor->getNavigator()->accept($link, $this->typeParser->parse('FSC\HateoasBundle\Model\Link'), $visitor);
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
