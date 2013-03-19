<?php

namespace FSC\HateoasBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class UrlGenerator implements UrlGeneratorInterface
{
    private $wrappedUrlGenerator;
    private $extraParameters;
    private $forceAbsolute;

    public function __construct(UrlGeneratorInterface $urlGenerator, $forceAbsolute = false)
    {
        $this->wrappedUrlGenerator = $urlGenerator;
        $this->extraParameters = array();
        $this->forceAbsolute = $forceAbsolute;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        return $this->wrappedUrlGenerator->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->wrappedUrlGenerator->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        $parameters = array_merge($this->extraParameters, $parameters);

        if ($this->forceAbsolute) {
            $absolute = true;
        }

        return $this->wrappedUrlGenerator->generate($name, $parameters, $absolute);
    }

    public function setForceAbsolute($forceAbsolute)
    {
        $this->forceAbsolute = $forceAbsolute;
    }

    public function setExtraParameters(array $extraParameters)
    {
        $this->extraParameters = $extraParameters;
    }

    public function getExtraParameters()
    {
        return $this->extraParameters;
    }

    public function getForceAbsolute()
    {
        return $this->forceAbsolute;
    }
}
