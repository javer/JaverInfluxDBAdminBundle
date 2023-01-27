<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use DateTimeInterface;
use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\AdminBundle\Filter\Filter as BaseFilter;
use Sonata\AdminBundle\Filter\Model\FilterData;
use TypeError;

abstract class Filter extends BaseFilter
{
    /**
     * @throws TypeError
     */
    public function apply(BaseProxyQueryInterface $query, FilterData $filterData): void
    {
        if (!$query instanceof ProxyQueryInterface) {
            throw new TypeError(sprintf('The query MUST implement "%s".', ProxyQueryInterface::class));
        }

        $this->filter($query, $this->getFieldName(), $filterData);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return [];
    }

    /* phpcs:ignore */
    abstract protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void;

    protected function applyWhere(ProxyQueryInterface $proxyQuery, string $condition, mixed $value = null): void
    {
        $proxyQuery->getQuery()->where($condition);

        $this->setActive(true);
    }

    protected function applyRange(
        ProxyQueryInterface $proxyQuery,
        DateTimeInterface $start,
        DateTimeInterface $stop,
    ): void
    {
        $proxyQuery->getQuery()->range($start, $stop);

        $this->setActive(true);
    }

    protected function quoteFieldValue(mixed $value): mixed
    {
        $isTag = $this->getFieldMapping()['tag'] ?? false;

        return $isTag ? sprintf('"%s"', $value) : $value;
    }
}
