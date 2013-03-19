<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FSC\HateoasBundle\Routing\RelationUrlGenerator;

use FSC\HateoasBundle\Model\Link;

abstract class AbstractLinkFactory
{
    protected $relationUrlGenerator;

    public function __construct(RelationUrlGenerator $relationUrlGenerator)
    {
        $this->relationUrlGenerator = $relationUrlGenerator;
    }

    public static function createLink($rel, $href)
    {
        $link = new Link();
        $link->setRel($rel);
        $link->setHref($href);

        return $link;
    }

    public function generateUrl($name, $parameters = array(), $absolute = false, $options = array())
    {
        ksort($parameters); // Have consistent url query strings, for the tests

        $alias = !empty($options['router']) ? $options['router'] : 'default';
        $urlGenerator = $this->relationUrlGenerator->getUrlGenerator($alias);

        return $urlGenerator->generate($name, $parameters, $absolute);
    }
}
