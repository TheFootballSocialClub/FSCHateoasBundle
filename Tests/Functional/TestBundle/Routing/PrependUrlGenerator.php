<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class PrependUrlGenerator implements UrlGeneratorInterface
{
    private $wrappedUrlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->wrappedUrlGenerator = $urlGenerator;
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
        return 'PREPEND' . $this->wrappedUrlGenerator->generate($name, $parameters, $absolute);
    }
}