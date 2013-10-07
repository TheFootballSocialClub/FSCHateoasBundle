<?php

namespace FSC\HateoasBundle\Serializer\EventSubscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\Event;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

use FSC\HateoasBundle\Factory\LinkFactoryInterface;
use FSC\HateoasBundle\Serializer\LinkSerializationHelper;
use FSC\HateoasBundle\Serializer\MetadataHelper;

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
    protected $metadataHelper;
    protected $linksJsonKey;
    protected $deferredLinks;

    public function __construct(LinkFactoryInterface $linkFactory, LinkSerializationHelper $linkSerializationHelper,
        MetadataHelper $metadataHelper, $linksJsonKey = null)
    {
        $this->linkFactory = $linkFactory;
        $this->linkSerializationHelper = $linkSerializationHelper;
        $this->metadataHelper = $metadataHelper;
        $this->linksJsonKey = $linksJsonKey ?: 'links';
        $this->deferredLinks = new \SplObjectStorage();
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
        $links = $this->getOnPostSerializeData($event);

        if (empty($links)) {
            return;
        }

        $event->getVisitor()->addData($this->linksJsonKey, $links);
    }

    public function getOnPostSerializeData(Event $event)
    {
        $object = $event->getObject();
        $context = $event->getContext();
        $metadataStack = $context->getMetadataStack();
        $visitingStack = $context->getVisitingStack();

        $links = $this->linkFactory->createLinks($object);
        if ($this->deferredLinks->contains($object)) {
            // $object contains inlined objects that had links

            $links = array_merge($this->deferredLinks->offsetGet($object), $links ?: array());
            $this->deferredLinks->detach($object);
        }

        if (null === $links) {
            return;
        }

        $parentObjectInlining = $this->metadataHelper->getParentObjectInlining($object, $context);
        if (null !== $parentObjectInlining) {
            if ($this->deferredLinks->contains($parentObjectInlining)) {
                $links = array_merge($this->deferredLinks->offsetGet($parentObjectInlining), $links);
            }

            // We need to defer the links serialization to the $parentObject
            $this->deferredLinks->attach($parentObjectInlining, $links);

            return;
        }

        $visitor = $event->getVisitor();

        return $this->linkSerializationHelper->createGenericLinksData($links, $visitor);
    }
}
