<?php

namespace FSC\HateoasBundle\Serializer\EventSubscriber;

use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;

use FSC\HateoasBundle\Model\PagedCollectionInterface;

class PagedCollectionSubscriber implements EventSubscriberInterface
{
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $object = $event->getObject();

        if ($object instanceof PagedCollectionInterface) {
            $event->setType('FSC\HateoasBundle\Model\PagedCollectionInterface');

            return;
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize'),
        );
    }
}
