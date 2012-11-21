<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\SerializerBundle\Serializer\Handler\SubscribingHandlerInterface;
use JMS\SerializerBundle\Serializer\EventDispatcher\Event;
use FSC\HateoasBundle\Serializer\EventSubscriber\LinkEventSubscriber;
use FSC\HateoasBundle\Serializer\EventSubscriber\EmbedderEventSubscriber;
use JMS\SerializerBundle\Serializer\GraphNavigator;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use Symfony\Component\Form\FormView;

use FSC\HateoasBundle\Serializer\XmlFormViewSerializer;

/**
 * Serializer a FormView
 */
class FormViewHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'xml',
                'type' => 'Symfony\Component\Form\FormView',
                'method' => 'serializeToXML',
            )
        );
    }

    protected $xmlFormViewSerializer;
    protected $embedderEventSubscriber;
    protected $linkEventSubscriber;

    public function __construct(XmlFormViewSerializer $xmlFormViewSerializer,
        EmbedderEventSubscriber $embedderEventSubscriber, LinkEventSubscriber $linkEventSubscriber)
    {
        $this->xmlFormViewSerializer = $xmlFormViewSerializer;
        $this->embedderEventSubscriber = $embedderEventSubscriber;
        $this->linkEventSubscriber = $linkEventSubscriber;
    }

    public function serializeToXML(XmlSerializationVisitor $visitor, FormView $formView, array $type)
    {
        if (null === $visitor->document) {
            $visitorClass = new \ReflectionClass(get_class($visitor));
            $defaultRootNameProperty = $visitorClass->getProperty('defaultRootName');
            $defaultRootNameProperty->setAccessible(true);
            if ('result' === $defaultRootNameProperty->getValue($visitor)) {
                $visitor->setDefaultRootName('form');
            }

            $visitor->document = $visitor->createDocument();
        }

        $this->embedderEventSubscriber->onPostSerializeXML(new Event($visitor, $formView, $type));
        $this->linkEventSubscriber->onPostSerializeXML(new Event($visitor, $formView, $type));

        $this->xmlFormViewSerializer->serialize($formView, $visitor->getCurrentNode());
    }
}
