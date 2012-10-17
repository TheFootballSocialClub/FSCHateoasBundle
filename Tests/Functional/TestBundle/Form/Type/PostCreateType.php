<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PostCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text');
    }

    public function getName()
    {
        return 'test_post_create';
    }
}
