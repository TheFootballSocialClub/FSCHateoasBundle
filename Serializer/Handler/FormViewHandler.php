<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use FSC\HateoasBundle\Serializer\EventSubscriber\LinkEventSubscriber;
use FSC\HateoasBundle\Serializer\EventSubscriber\EmbedderEventSubscriber;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\ObjectEvent;
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
        EmbedderEventSubscriber $embedderEventSubscriber, LinkEventSubscriber $linkEventSubscriber
    ) {
        $this->xmlFormViewSerializer = $xmlFormViewSerializer;
        $this->embedderEventSubscriber = $embedderEventSubscriber;
        $this->linkEventSubscriber = $linkEventSubscriber;
    }

    public function serializeToXML(XmlSerializationVisitor $visitor, FormView $formView, array $type, Context $context)
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

        $this->embedderEventSubscriber->onPostSerializeXML(new ObjectEvent($context, $formView, $type));
        $this->linkEventSubscriber->onPostSerializeXML(new ObjectEvent($context, $formView, $type));

        $this->xmlFormViewSerializer->serialize($formView, $visitor->getCurrentNode());
    }
}
