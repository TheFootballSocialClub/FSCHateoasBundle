<?php

namespace FSC\HateoasBundle\Factory;

interface ParametersFactoryInterface
{
    /**
     * @param object $data
     * @param array  $parameters
     *
     * @return mixed
     */
    public function createParameters($data, $parameters);

    /**
     * @param object $object
     * @param array  $excludeIf
     *
     * @return boolean
     */
    public function createExclude($object, $excludeIf);
}
