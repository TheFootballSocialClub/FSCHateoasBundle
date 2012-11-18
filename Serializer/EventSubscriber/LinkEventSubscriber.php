<?php

namespace FSC\HateoasBundle\Serializer\EventSubscriber;

use JMS\SerializerBundle\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\SerializerBundle\Serializer\EventDispatcher\Events;
use JMS\SerializerBundle\Serializer\EventDispatcher\Event;

use FSC\HateoasBundle\Factory\LinkFactoryInterface;
use FSC\HateoasBundle\Serializer\LinkSerializationHelper;

/**
 * Adds links to serialized objects based on hateoas metadata
 */
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

    protected $linkFactory;
    protected $linkSerializationHelper;
    protected $linksCollectionName;

    public function __construct(LinkFactoryInterface $linkFactory, LinkSerializationHelper $linkSerializationHelper, array $jsonOptions)
    {
        $this->linkFactory = $linkFactory;
        $this->linkSerializationHelper = $linkSerializationHelper;
        $this->jsonOptions = $jsonOptions;
        $this->linksCollectionName = $jsonOptions['links'];
    }

    public function onPostSerializeXML(Event $event)
    {
        if (null === ($links = $this->linkFactory->createLinks($event->getObject(), $event->getType()))) {
            return;
        }

        $this->linkSerializationHelper->addLinksToXMLSerialization($links, $event->getVisitor());
    }

    public function onPostSerialize(Event $event)
    {
        if (null === ($links = $this->getOnPostSerializeData($event))) {
            return;
        }

        $event->getVisitor()->addData('links', $links);
    }

    public function getOnPostSerializeData(Event $event)
    {
        if (null === ($links = $this->linkFactory->createLinks($event->getObject()))) {
            return;
        }

        $visitor = $event->getVisitor();

        return $this->linkSerializationHelper->createGenericLinksData($links, $visitor);
        // $visitor->addData($this->linksCollectionName, $this->linkSerializationHelper->createGenericLinksData($links, $visitor));
    }
}
