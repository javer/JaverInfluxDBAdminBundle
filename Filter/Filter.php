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

    /**
     * Quotes field name.
     *
     * @param string $field
     *
     * @return string
     */
    protected function quoteFieldName(string $field): string
    {
        $isId = $this->getFieldMapping()['id'] ?? false;

        return $isId ? $field : sprintf('"%s"', $field);
    }

    /**
     * Quotes field value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function quoteFieldValue($value)
    {
        $isTag = $this->getFieldMapping()['tag'] ?? false;

        return $isTag ? sprintf("'%s'", $value) : $value;
    }
}
