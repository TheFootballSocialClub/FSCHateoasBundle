<?php

namespace FSC\HateoasBundle\Serializer\EventSubscriber;

use JMS\SerializerBundle\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\SerializerBundle\Serializer\EventDispatcher\Events;
use JMS\SerializerBundle\Serializer\EventDispatcher\Event;
use JMS\SerializerBundle\Serializer\TypeParser;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Serializer\GenericSerializationVisitor;

use FSC\HateoasBundle\Factory\LinkFactoryInterface;

class LinkEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'event' => Events::POST_SERIALIZE,
                'format' => $format,
                'method' => 'onPostSerialize'.('xml' == $format ? 'xml' : ''),
            );
        }

        return $methods;
    }

    /**
     * @var LinkFactoryInterface
     */
    protected $linkFactory;
    protected $typeParser;

    public function __construct(LinkFactoryInterface $linkFactory, TypeParser $typeParser = null)
    {
        $this->linkFactory = $linkFactory;
        $this->typeParser = $typeParser ?: new TypeParser();
    }

    public function onPostSerializeXML(Event $event)
    {
        if (null === ($links = $this->linkFactory->createLinks($event->getObject(), $event->getType()))) {
            return;
        }

        $this->addLinksToXMLSerialization($links, $event->getVisitor());
    }

    public function onPostSerialize(Event $event)
    {
        if (null === ($links = $this->linkFactory->createLinks($event->getObject()))) {
            return;
        }

        $visitor = $event->getVisitor();
        $visitor->addData('links', $this->createGenericLinksData($links, $visitor));
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
        return $visitor->getNavigator()->accept($links, $this->typeParser->parse('array<FSC\HateoasBundle\Model\Link>'), $visitor);
    }
}
