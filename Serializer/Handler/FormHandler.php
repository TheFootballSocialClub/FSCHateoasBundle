<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\SerializerBundle\Serializer\Handler\SubscribingHandlerInterface;
use JMS\SerializerBundle\Serializer\GraphNavigator;
use JMS\SerializerBundle\Serializer\VisitorInterface;
use Symfony\Component\Form\Form;

/**
 * FormInterface -> FormView
 */
class FormHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => 'Symfony\Component\Form\Form',
                'method' => 'serialize',
            );
        }

        return $methods;
    }

    public function serialize(VisitorInterface $visitor, Form $form, array $type)
    {
        $formView = $form->createView();

        return $visitor->getNavigator()->accept($formView, null, $visitor);
    }
}
