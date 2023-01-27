<?php

namespace Javer\InfluxDB\AdminBundle\DependencyInjection;

use Sonata\AdminBundle\DependencyInjection\AbstractSonataAdminExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class JaverInfluxDBAdminExtension extends AbstractSonataAdminExtension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configs = $this->fixTemplatesConfiguration($configs, $container);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('javer_influxdb_admin.templates', $config['templates']);

        $container->getDefinition('sonata.admin.builder.influxdb_list')
            ->replaceArgument(1, $config['templates']['types']['list']);

        $container->getDefinition('sonata.admin.builder.influxdb_show')
            ->replaceArgument(1, $config['templates']['types']['show']);
    }
}
