<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\Util\PropertyPath;
use Metadata\MetadataFactoryInterface;
use Pagerfanta\PagerfantaInterface;

use FSC\HateoasBundle\Model\Link;
use FSC\HateoasBundle\Metadata\ClassMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;

class LinkFactory implements LinkFactoryInterface, PagerLinkFactoryInterface
{
    protected $urlGenerator;
    protected $metadataFactory;
    protected $parametersFactory;

    public function __construct(UrlGeneratorInterface $urlGenerator, MetadataFactoryInterface $metadataFactory,
                                ParametersFactoryInterface $parametersFactory)
    {
        $this->urlGenerator = $urlGenerator;
        $this->metadataFactory = $metadataFactory;
        $this->parametersFactory = $parametersFactory;
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

    public function createPagerLinks(PagerfantaInterface $pager, $route, $defaultRouteParameters)
    {
        if (!isset($defaultRouteParameters['page'])) {
            $defaultRouteParameters['page'] = $pager->getCurrentPage();
        }
        if (!isset($defaultRouteParameters['limit'])) {
            $defaultRouteParameters['limit'] = $pager->getMaxPerPage();
        }

        $links = array();
        $links[] = $this->createLink('self', $this->generateUrl($route, $defaultRouteParameters));
        $links[] = $this->createLink('first', $this->generateUrl(
            $route,
            array_merge($defaultRouteParameters, array('page' => '1'))
        ));
        $links[] = $this->createLink('last', $this->generateUrl(
            $route,
            array_merge($defaultRouteParameters, array('page' => $pager->getNbPages()))
        ));

        if ($pager->hasPreviousPage()) {
            $links[] = $this->createLink('next', $this->generateUrl(
                $route,
                array_merge($defaultRouteParameters, array('page' => $pager->getPreviousPage()))
            ));
        }

        if ($pager->hasNextPage()) {
            $links[] = $this->createLink('next', $this->generateUrl(
                $route,
                array_merge($defaultRouteParameters, array('page' => $pager->getNextPage()))
            ));
        }

        return $links;
    }

    public function createLinksFromMetadata(ClassMetadataInterface $classMetadata, $object)
    {
        $links = array();

        foreach ($classMetadata->getRelations() as $relationMetadata) {
            $links[] = $this->createLinkFromMetadata($relationMetadata, $object);
        }

        return $links;
    }

    public function createLinkFromMetadata(RelationMetadataInterface $relationMetadata, $object)
    {
        $href = $this->generateUrl($relationMetadata->getRoute(), $this->parametersFactory->createParameters($object, $relationMetadata->getParams()));

        return $this->createLink($relationMetadata->getRel(), $href);
    }

    public static function createLink($rel, $href)
    {
        $link = new Link();
        $link->setRel($rel);
        $link->setHref($href);

        return $link;
    }

    public function generateUrl($name, $parameters = array())
    {
        ksort($parameters); // Have consistent url query strings, for the tests

        return $this->urlGenerator->generate($name, $parameters, true);
    }


}
