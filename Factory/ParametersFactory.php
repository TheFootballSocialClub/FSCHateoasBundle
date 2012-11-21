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
        $self = $this;
        array_walk($parameters, function (&$value, $key) use ($data, $self) {
            if (is_string($value) && in_array(substr($value, 0, 1), array('.', '['))) {
                $propertyPath = new PropertyPath(preg_replace('/^\./', '', $value));
                $value = $propertyPath->getValue($data);

                return;
            } elseif ('@' === $value) {
                $value = $data;
            } elseif (is_array($value)) {
                $value = $self->createParameters($data, $value);
            }
        });

        return $parameters;
    }
}
