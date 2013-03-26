<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\EventDispatcher\Event;
use FSC\HateoasBundle\Serializer\EventSubscriber\LinkEventSubscriber;
use FSC\HateoasBundle\Serializer\EventSubscriber\EmbedderEventSubscriber;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Metadata\MetadataFactoryInterface;
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
    protected $serializerMetadataFactory;

    public function __construct(XmlFormViewSerializer $xmlFormViewSerializer,
        EmbedderEventSubscriber $embedderEventSubscriber, LinkEventSubscriber $linkEventSubscriber,
        MetadataFactoryInterface $serializerMetadataFactory
    ) {
        $this->xmlFormViewSerializer = $xmlFormViewSerializer;
        $this->embedderEventSubscriber = $embedderEventSubscriber;
        $this->linkEventSubscriber = $linkEventSubscriber;
        $this->serializerMetadataFactory = $serializerMetadataFactory;
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

        $context = SerializationContext::create();
        $context->initialize('xml', $visitor, $visitor->getNavigator(), $this->serializerMetadataFactory);

        $this->embedderEventSubscriber->onPostSerializeXML(new ObjectEvent($context, $formView, $type));
        $this->linkEventSubscriber->onPostSerializeXML(new ObjectEvent($context, $formView, $type));

        $this->xmlFormViewSerializer->serialize($formView, $visitor->getCurrentNode());
    }
}
