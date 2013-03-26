<?php

namespace FSC\HateoasBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use FSC\HateoasBundle\DependencyInjection\Compiler\UrlGeneratorCompilerPass;

class FSCHateoasBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new UrlGeneratorCompilerPass());
    }
}
