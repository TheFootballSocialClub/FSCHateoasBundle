<?php

namespace FSC\HateoasBundle\Serializer\EventSubscriber;

use JMS\SerializerBundle\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\SerializerBundle\Serializer\EventDispatcher\Events;
use JMS\SerializerBundle\Serializer\EventDispatcher\Event;

use FSC\HateoasBundle\Model\Link;

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
                'method' => 'onPostSerialize'.strtoupper($format)
            );
        }

        return $methods;
    }

    public function onPostSerializeXML(Event $event)
    {
        if (null === ($links = $this->getLinks($event))) {
            return;
        }

        $xmlSerializationVisitor = $event->getVisitor();
        $navigator = $xmlSerializationVisitor->getNavigator();

        $currentNode = $xmlSerializationVisitor->getCurrentNode(); // \DOMElement ... :)
        $document = $xmlSerializationVisitor->getDocument();

        foreach ($links as $link) {
            $entryNode = $document->createElement('link');
            $currentNode->appendChild($entryNode);
            $xmlSerializationVisitor->setCurrentNode($entryNode);

            if (null !== $node = $navigator->accept($link, null, $xmlSerializationVisitor)) {
                $xmlSerializationVisitor->getCurrentNode()->appendChild($node);
            }

            $xmlSerializationVisitor->revertCurrentNode();
        }
    }

    public function onPostSerializeJSON(Event $event)
    {
        if (null === ($links = $this->getLinks($event))) {
            return;
        }

        $this->addLinksToGenericVisitor($event, $links);
    }

    public function onPostSerializeYML(Event $event)
    {
        if (null === ($links = $this->getLinks($event))) {
            return;
        }

        $this->addLinksToGenericVisitor($event, $links);
    }

    protected function getLinks(Event $event)
    {
        if ($event->getObject() instanceof Link) {
            return null;
        }

        $link1 = new Link();
        $link1->setHref('http://symfony.com/hey');
        $link1->setRel('self');

        $link2 = new Link();
        $link2->setHref('http://symfony.com/fabpot');
        $link2->setRel('alternate');

        return array(
            $link1->getRel() => $link1,
            $link2->getRel() => $link2,
        );
    }

    protected function addLinksToGenericVisitor(Event $event, $links)
    {
        $data = $event->getVisitor()->getNavigator()->accept($links, $this->getLinksType(), $event->getVisitor());
        $event->getVisitor()->addData('links', $data);
    }

    protected static function getLinksType()
    {
        return array(
            'name' => 'array',
            'params' => array(
                array('name' => 'string'),
                array('name' => 'FSC\HateoasBundle\Model\Link'),
            )
        );
    }
}
