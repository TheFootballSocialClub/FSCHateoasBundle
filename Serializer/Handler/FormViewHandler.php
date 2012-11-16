<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\SerializerBundle\Serializer\Handler\SubscribingHandlerInterface;
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

    public function __construct(XmlFormViewSerializer $xmlFormViewSerializer)
    {
        $this->xmlFormViewSerializer = $xmlFormViewSerializer;
    }

    public function serializeToXML(XmlSerializationVisitor $visitor, FormView $formView, array $resultsType)
    {
        if (null === $visitor->document) {
            $visitor->document = $visitor->createDocument();
        }

        $this->xmlFormViewSerializer->serialize($formView, $visitor->getCurrentNode());
    }
}
