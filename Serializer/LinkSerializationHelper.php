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
        $result = array();

        foreach ($links as $link) {
            $rel = $link->getRel();
            $link->setRel(null);    // To avoid serialization

            $serializedLink = $visitor->getNavigator()->accept($link, $this->typeParser->parse('FSC\HateoasBundle\Model\Link'), $visitor);
            if (!empty($result[$rel])) {
                if (!empty($result[$rel]['href'])) {
                    $oldLink = $result[$rel];
                    $result[$rel] = array($oldLink);
                }

                $result[$rel] []= $serializedLink;
            } else {
                $result[$rel] = $serializedLink;
            }


        }

        return $result;
    }
}
