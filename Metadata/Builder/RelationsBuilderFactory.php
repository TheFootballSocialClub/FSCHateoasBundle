<?php

namespace FSC\HateoasBundle\Metadata\Builder;

class RelationsBuilderFactory
{
    public static function create()
    {
        return new RelationsBuilder();
    }
}
