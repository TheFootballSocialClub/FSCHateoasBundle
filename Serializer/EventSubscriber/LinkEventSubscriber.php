<?php

namespace FSC\HateoasBundle\Serializer\EventSubscriber;

use JMS\SerializerBundle\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\SerializerBundle\Serializer\EventDispatcher\Events;
use JMS\SerializerBundle\Serializer\EventDispatcher\Event;
use JMS\SerializerBundle\Serializer\TypeParser;

use FSC\HateoasBundle\Model\Link;
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
     * @var array 'type' => ('name' => '', 'params' => array(...))
     */
    protected static $serializerTypeCache;

    /**
     * @var LinkFactoryInterface
     */
    protected $linkFactory;

    public function __construct(LinkFactoryInterface $linkFactory)
    {
        $this->linkFactory = $linkFactory;
    }

    public function onPostSerializeXML(Event $event)
    {
        if (null === ($links = $this->linkFactory->createLinks($event->getObject(), $event->getType()))) {
            return;
        }

        $visitor = $event->getVisitor();

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

    public function onPostSerialize(Event $event)
    {
        if (null === ($links = $this->linkFactory->createLinks($event->getObject()))) {
            return;
        }

        $links = $this->createLinksArray($links);

        $data = $event->getVisitor()->getNavigator()->accept($links, $this->getSerializerType('array<string,string>'), $event->getVisitor());
        $event->getVisitor()->addData('links', $data);
    }

    protected static function getSerializerType($type)
    {
        if (isset(self::$serializerTypeCache[$type])) {
            return self::$serializerTypeCache[$type];
        }

        // Todo create a CachedTypeParser that would be wrapper
        $typeParser = new TypeParser();

        return self::$serializerTypeCache[$type] = $typeParser->parse($type);
    }

    protected static function createLinksArray($links)
    {
        $newLinks = array();

        foreach ($links as $link) {
            $newLinks[$link->getRel()] = $link->getHref();
        }

        return $newLinks;
    }
}
