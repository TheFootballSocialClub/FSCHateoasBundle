<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use FSC\HateoasBundle\Model\Link;

abstract class AbstractLinkFactory
{
    protected $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
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

        return $this->urlGenerator->generate($name, $parameters);
    }
}
