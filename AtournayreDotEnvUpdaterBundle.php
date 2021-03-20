<?php

namespace Atournayre\DotEnvUpdaterBundle;

use Atournayre\DotEnvUpdaterBundle\DependencyInjection\AtournayreDotEnvUpdaterExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AtournayreDotEnvUpdaterBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new AtournayreDotEnvUpdaterExtension();
        }

        return $this->extension;
    }
}
