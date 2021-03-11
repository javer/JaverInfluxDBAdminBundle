<?php

namespace Javer\InfluxDB\AdminBundle\Exporter;

use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQuery;
use LogicException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Sonata\Exporter\Source\DoctrineODMQuerySourceIterator;
use Sonata\Exporter\Source\SourceIteratorInterface;

/**
 * Class DataSource
 *
 * @package Javer\InfluxDB\AdminBundle\Exporter
 */
class DataSource implements DataSourceInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws LogicException
     */
    public function createIterator(ProxyQueryInterface $query, array $fields): SourceIteratorInterface
    {
        if (!$query instanceof ProxyQuery) {
            throw new LogicException(sprintf('Argument 1 MUST be an instance of "%s"', ProxyQuery::class));
        }

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        return new DoctrineODMQuerySourceIterator($query->getQuery(), $fields);
    }
}
