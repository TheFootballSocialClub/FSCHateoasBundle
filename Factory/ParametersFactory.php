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
            if ('=' === substr($value, 0, 1)) {
                $value = substr($value, 1);

                return;
            }

            $propertyPath = new PropertyPath($value);
            $value = $propertyPath->getValue($data);
        });

        return $parameters;
    }
}
