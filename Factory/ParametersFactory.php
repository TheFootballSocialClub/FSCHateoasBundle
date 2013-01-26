<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ParametersFactory implements ParametersFactoryInterface
{
    private $propertyAccessor;

    /**
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function createParameters($data, $parameters)
    {
        $self = $this;
        $propertyAccessor = $this->propertyAccessor;
        array_walk($parameters, function (&$value, $key) use ($data, $self, $propertyAccessor) {
            if (is_string($value) && in_array(substr($value, 0, 1), array('.', '['))) {
                $propertyPath = new PropertyPath(preg_replace('/^\./', '', $value));
                $value = $propertyAccessor->getValue($data, $propertyPath);
            } elseif ('@' === $value) {
                $value = $data;
            } elseif (is_array($value)) {
                $value = $self->createParameters($data, $value);
            }
        });

        return $parameters;
    }
}
