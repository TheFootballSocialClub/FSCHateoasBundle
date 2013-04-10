<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\GenericSerializationVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\ObjectEvent;

use FSC\HateoasBundle\Model\HalPagerfanta;
use FSC\HateoasBundle\Serializer\EventSubscriber\EmbedderEventSubscriber;
use FSC\HateoasBundle\Serializer\EventSubscriber\LinkEventSubscriber;

class HalPagerfantaHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => 'FSC\HateoasBundle\Model\HalPagerfanta',
                'method' => 'serializeTo'.('xml' == $format ? 'XML' : 'Array'),
            );
        }

        return $methods;
    }

    protected $embedderEventSubscriber;
    protected $linkEventSubscriber;
    protected $linksJsonKey;
    protected $relationsJsonKey;

    public function __construct(
        EmbedderEventSubscriber $embedderEventSubscriber,
        LinkEventSubscriber $linkEventSubscriber,
        $linksKey = null,
        $relationsKey = null
    ) {
        $this->embedderEventSubscriber = $embedderEventSubscriber;
        $this->linkEventSubscriber = $linkEventSubscriber;
        $this->linksJsonKey = $linksKey ?: 'links';
        $this->relationsJsonKey = $relationsKey ?: 'relations';
    }

    public function serializeToXML(XmlSerializationVisitor $visitor, HalPagerfanta $halPager, array $type, Context $context)
    {
        return $visitor->getNavigator()->accept($halPager->getPager(), null, $context);
    }

    public function serializeToArray(GenericSerializationVisitor $visitor, HalPagerfanta $halPager, array $type, Context $context)
    {
        $shouldSetRoot = null === $visitor->getRoot();

        $pager = $halPager->getPager();
        $data = array(
            'page' => $pager->getCurrentPage(),
            'limit' => $pager->getMaxPerPage(),
            'total' => $pager->getNbResults(),
        );

        $context->stopVisiting($halPager); // Make sure the visiting behavior is the same as for normal events to call the getOnPostSerializeData

        if (null !== ($links = $this->linkEventSubscriber->getOnPostSerializeData(new ObjectEvent($context, $pager, $type)))) {
            $data[$this->linksJsonKey] = $links;
        }

        if (null !== ($relations = $this->embedderEventSubscriber->getOnPostSerializeData(new ObjectEvent($context, $pager, $type)))) {
            $data[$this->relationsJsonKey] = $relations;
        }

        $context->startVisiting($halPager);

        $resultsType = array(
            'name' => 'array',
        );
        if (isset($type['params'])) {
            $resultsType['params'] = $type['params'];
        }
        $data[$this->relationsJsonKey][$halPager->getRel()] = $visitor->getNavigator()->accept($pager->getCurrentPageResults(), $resultsType, $context);

        if ($shouldSetRoot) {
            $visitor->setRoot($data);
        }

        return $data;
    }
}
