<?php

namespace FSC\HateoasBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use FSC\HateoasBundle\DependencyInjection\FSCHateoasExtension;

class FSCHateoasBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new FSCHateoasExtension();
        }

        return $this->extension;
    }
}
