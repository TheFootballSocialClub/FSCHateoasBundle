<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\SerializerBundle\Serializer\Handler\SubscribingHandlerInterface;
use JMS\SerializerBundle\Serializer\GraphNavigator;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Serializer\GenericSerializationVisitor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use FSC\HateoasBundle\Model\RouteAwareFormView;

class RouteAwareFormViewHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => 'FSC\HateoasBundle\Model\RouteAwareFormView',
                'method' => 'serializeTo'.('xml' == $format ? 'XML' : 'Array'),
            );
        }

        return $methods;
    }

    protected $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function serializeToXML(XmlSerializationVisitor $visitor, RouteAwareFormView $routeAwareFormView, array $resultsType)
    {
        if (null === $visitor->document) {
            $visitor->document = $visitor->createDocument(null, null, true);
        }

        $currentNode = $visitor->getCurrentNode(); /** @var $currentNode \DOMElement */

        $currentNode->setAttribute('method', $this->getMethod($routeAwareFormView));
        $currentNode->setAttribute('action', $this->getActionUrl($routeAwareFormView));

        return $visitor->getNavigator()->accept($routeAwareFormView->getFormView(), null, $visitor);
    }

    public function serializeToArray(GenericSerializationVisitor $visitor, RouteAwareFormView $routeAwareFormView, array $type)
    {
        $data = $visitor->getNavigator()->accept($routeAwareFormView->getFormView(), null, $visitor);

        $data['method'] = $this->getMethod($routeAwareFormView);
        $data['action'] = $this->getActionUrl($routeAwareFormView);

        return $data;
    }

    protected function getMethod(RouteAwareFormView $routeAwareFormView)
    {
        return strtoupper($routeAwareFormView->getMethod());
    }

    protected function getActionUrl(RouteAwareFormView $routeAwareFormView)
    {
        return $this->urlGenerator->generate($routeAwareFormView->getRoute(), $routeAwareFormView->getRouteParameters(), true);
    }
}
