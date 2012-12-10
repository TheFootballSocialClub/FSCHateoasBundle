<?php

namespace FSC\HateoasBundle\Controller;

trait HateoasTrait
{
    protected function generateRelationUrl($object, $rel)
    {
        return $this->container->get('fsc_hateoas.routing.relation_url_generator')->generateUrl($object, $rel);
    }

    protected function generateSelfUrl($object)
    {
        return $this->generateRelationUrl($object, 'self');
    }
}
