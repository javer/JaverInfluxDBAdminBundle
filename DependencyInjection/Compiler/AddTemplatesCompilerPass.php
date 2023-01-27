<?php

namespace Javer\InfluxDB\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AddTemplatesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            if (!isset($attributes[0]['manager_type']) || $attributes[0]['manager_type'] !== 'influxdb') {
                continue;
            }

            $definition = $container->getDefinition($id);
            $templates = $container->getParameter('javer_influxdb_admin.templates');

            if (!$definition->hasMethodCall('setFormTheme')) {
                $definition->addMethodCall('setFormTheme', [$templates['form']]);
            }

            if (!$definition->hasMethodCall('setFilterTheme')) {
                $definition->addMethodCall('setFilterTheme', [$templates['filter']]);
            }
        }
    }
}
