<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

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

    protected function applyWhere(ProxyQueryInterface $proxyQuery, string $condition): void
    {
        $proxyQuery->getQuery()->where($condition);

        $this->setActive(true);
    }

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
