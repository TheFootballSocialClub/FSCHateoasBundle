<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Form\Util\PropertyPath;

class ParametersFactory implements ParametersFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createParameters($data, $parameters)
    {
        array_walk($parameters, function (&$value, $key) use ($data) {
            $propertyPath = new PropertyPath($value);
            $value = $propertyPath->getValue($data);
        });

        return $parameters;
    }
}
