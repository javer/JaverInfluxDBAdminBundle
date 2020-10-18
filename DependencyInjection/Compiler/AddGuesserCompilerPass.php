<?php

namespace Javer\InfluxDB\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddGuesserCompilerPass
 *
 * @package Javer\InfluxDB\AdminBundle\DependencyInjection\Compiler
 */
class AddGuesserCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $this->addGuessersToBuilder(
            $container,
            'sonata.admin.guesser.influxdb_list_chain',
            'sonata.admin.guesser.influxdb_list'
        );

        $this->addGuessersToBuilder(
            $container,
            'sonata.admin.guesser.influxdb_datagrid_chain',
            'sonata.admin.guesser.influxdb_datagrid'
        );

        $this->addGuessersToBuilder(
            $container,
            'sonata.admin.guesser.influxdb_show_chain',
            'sonata.admin.guesser.influxdb_show'
        );
    }

    /**
     * Add guessers to builder.
     *
     * @param ContainerBuilder $container
     * @param string           $builderDefinitionId
     * @param string           $guessersTag
     */
    private function addGuessersToBuilder(
        ContainerBuilder $container,
        string $builderDefinitionId,
        string $guessersTag
    ): void
    {
        if (!$container->hasDefinition($builderDefinitionId)) {
            return;
        }

        $definition = $container->getDefinition($builderDefinitionId);
        $services = [];

        foreach ($container->findTaggedServiceIds($guessersTag) as $id => $attributes) {
            $services[] = new Reference($id);
        }

        $definition->replaceArgument(0, $services);
    }
}
