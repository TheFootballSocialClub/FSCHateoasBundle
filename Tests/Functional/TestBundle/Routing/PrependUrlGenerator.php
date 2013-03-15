<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class PrependUrlGenerator implements UrlGeneratorInterface
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
    public function generate($name, $parameters = array(), $absolute = false, $options = array())
    {
        $parameters = array_merge($this->extraParameters, $parameters);

        if ($this->forceAbsolute) {
            $absolute = true;
        }

        return 'PREPEND' . $this->wrappedUrlGenerator->generate($name, $parameters, $absolute);
    }
}