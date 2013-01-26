<?php

namespace FSC\HateoasBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use FSC\HateoasBundle\DependencyInjection\Compiler\ConfigurationCheckPass;

class FSCHateoasBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigurationCheckPass());
    }
}
