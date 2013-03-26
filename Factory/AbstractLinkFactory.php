<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FSC\HateoasBundle\Routing\RelationUrlGenerator;

use FSC\HateoasBundle\Model\Link;

abstract class AbstractLinkFactory
{
    protected $relationUrlGenerator;
    protected $forceAbsolute;

    public function __construct(RelationUrlGenerator $relationUrlGenerator, $forceAbsolute = true)
    {
        $this->relationUrlGenerator = $relationUrlGenerator;
        $this->forceAbsolute = $forceAbsolute;
    }

    public static function createLink($rel, $href, $relationAttributes = null)
    {
        $link = new Link();
        $link->setRel($rel);
        $link->setHref($href);
        $link->setRelationAttributes($relationAttributes);

        return $link;
    }

    public function generateUrl($name, $parameters = array(), $options = array())
    {
        ksort($parameters); // Have consistent url query strings, for the tests

        $alias = !empty($options['router']) ? $options['router'] : 'default';
        $urlGenerator = $this->relationUrlGenerator->getUrlGenerator($alias);

        $absolute = isset($options['absolute']) ? $options['absolute'] : $this->forceAbsolute;

        return $urlGenerator->generate($name, $parameters, $absolute);
    }
}
