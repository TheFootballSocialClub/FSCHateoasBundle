<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\Util\PropertyPath;
use Metadata\MetadataFactoryInterface;

use FSC\HateoasBundle\Model\Link;
use FSC\HateoasBundle\Metadata\ClassMetadataInterface;

class LinkFactory implements LinkFactoryInterface
{
    protected $router;
    protected $metadataFactory;

    public function __construct(RouterInterface $router, MetadataFactoryInterface $metadataFactory)
    {
        $this->router = $router;
        $this->metadataFactory = $metadataFactory;
    }

    public function createLinks($object)
    {
        if ($object instanceof Link) {
            return;
        }

        if (null === ($classMetadata = $this->metadataFactory->getMetadataForClass(get_class($object)))) {
            return;
        }

        return $this->createLinksFromMetadata($classMetadata, $object);
    }

    public function createLinksFromMetadata(ClassMetadataInterface $classMetadata, $object)
    {
        $links = array();

        foreach ($classMetadata->getLinks() as $linkMeta) {
            $href = $this->router->generate($linkMeta['route'], $this->createRouteParameters($linkMeta['params'], $object), true);
            $links[] = $this->createLink($linkMeta['rel'], $href);
        }

        return $links;
    }

    public static function createLink($rel, $href)
    {
        $link = new Link();
        $link->setRel($rel);
        $link->setHref($href);

        return $link;
    }

    public static function createRouteParameters($parameters, $object)
    {
        array_walk($parameters, function (&$value, $key) use ($object) {
            $propertyPath = new PropertyPath($value);
            $value = $propertyPath->getValue($object);
        });

        return $parameters;
    }
}
