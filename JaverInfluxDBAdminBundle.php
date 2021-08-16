<?php

namespace Javer\InfluxDB\AdminBundle;

use Javer\InfluxDB\AdminBundle\DependencyInjection\Compiler\AddGuesserCompilerPass;
use Javer\InfluxDB\AdminBundle\DependencyInjection\Compiler\AddTemplatesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class JaverInfluxDBAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddGuesserCompilerPass());
        $container->addCompilerPass(new AddTemplatesCompilerPass());
    }
}
