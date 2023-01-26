<?php

namespace Javer\InfluxDB\AdminBundle\Datagrid;

use Javer\InfluxDB\ODM\Query\Query;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;

final class ProxyQuery implements BaseProxyQueryInterface, ProxyQueryInterface
{
    private ?string $sortBy = null;

    private ?string $sortOrder = null;

    private ?int $firstResult = null;

    private ?int $maxResults = null;

    public function __construct(
        private Query $query,
    )
    {
    }

    /**
     * Call method.
     *
     * @param string  $name
     * @param mixed[] $args
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return call_user_func_array([$this->query, $name], $args);
    }

    public function __clone()
    {
        $this->query = clone $this->query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(): array
    {
        $query = clone $this->query;

        $sortBy = $this->getSortBy();

        if ($sortBy) {
            $query->orderBy($sortBy, $this->getSortOrder());
        }

        return $query->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function setSortBy(array $parentAssociationMappings, array $fieldMapping): self
    {
        $this->sortBy = $fieldMapping['fieldName'];

        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortOrder(string $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getSortOrder(): ?string
    {
        return $this->sortOrder;
    }

    public function setFirstResult(?int $firstResult): self
    {
        $this->firstResult = $firstResult;

        $this->query->offset($firstResult ?? 0);

        return $this;
    }

    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    public function setMaxResults(?int $maxResults): self
    {
        $this->maxResults = $maxResults;

        $this->query->limit($maxResults ?? 0);

        return $this;
    }

    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }
}
