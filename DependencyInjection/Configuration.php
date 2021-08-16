<?php

namespace Javer\InfluxDB\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('javer_influx_db_admin');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('form')
                            ->prototype('scalar')->end()
                            ->defaultValue(['@SonataAdmin/Form/form_admin_fields.html.twig'])
                        ->end()
                        ->arrayNode('filter')
                            ->prototype('scalar')->end()
                            ->defaultValue(['@SonataAdmin/Form/filter_admin_fields.html.twig'])
                        ->end()
                        ->arrayNode('types')
                            ->children()
                                ->arrayNode('list')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('show')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
