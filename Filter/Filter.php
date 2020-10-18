<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Javer\InfluxDB\ODM\Query\Query;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Filter as BaseFilter;

/**
 * Class Filter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
abstract class Filter extends BaseFilter
{
    protected bool $active = false;

    /**
     * {@inheritDoc}
     */
    public function apply($query, $value): void
    {
        $this->value = $value;

        $field = $this->getFieldMapping()['name'];

        $this->filter($query, null, $field, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return [];
    }

    /**
     * Apply where.
     *
     * @param ProxyQueryInterface $proxyQuery
     * @param string              $condition
     */
    protected function applyWhere(ProxyQueryInterface $proxyQuery, string $condition): void
    {
        /** @var Query $queryBuilder */
        $queryBuilder = $proxyQuery;

        $queryBuilder->where($condition);

        $this->active = true;
    }
}
