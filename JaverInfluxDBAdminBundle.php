<?php

namespace Javer\InfluxDB\AdminBundle;

use Javer\InfluxDB\AdminBundle\DependencyInjection\Compiler\AddGuesserCompilerPass;
use Javer\InfluxDB\AdminBundle\DependencyInjection\Compiler\AddTemplatesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class JaverInfluxDBAdminBundle
 *
 * @package Javer\InfluxDB\AdminBundle
 */
class JaverInfluxDBAdminBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddGuesserCompilerPass());
        $container->addCompilerPass(new AddTemplatesCompilerPass());
    }
}
