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

    public static function createLink($rel, $href, $templated = false)
    {
        $link = new Link();
        $link->setRel($rel);
        $link->setHref($href);
        $link->setTemplated($templated);

        return $link;
    }

    public function generateUrl($name, $parameters = array(), $options = array())
    {
        ksort($parameters); // Have consistent url query strings, for the tests

        $alias = !empty($options['router']) ? $options['router'] : 'default';
        $urlGenerator = $this->relationUrlGenerator->getUrlGenerator($alias);

        $absolute = !empty($options['absolute']) ? $options['absolute'] : false;

        return $urlGenerator->generate($name, $parameters, $absolute);
    }
}
