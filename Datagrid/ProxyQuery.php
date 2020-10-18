<?php

namespace Javer\InfluxDB\AdminBundle\Datagrid;

use BadMethodCallException;
use Javer\InfluxDB\ODM\Query\Query;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * Class ProxyQuery
 *
 * @package Javer\InfluxDB\AdminBundle\Datagrid
 */
class ProxyQuery implements ProxyQueryInterface
{
    private Query $query;

    private ?string $sortBy = null;

    private ?string $sortOrder = null;

    private ?int $firstResult = null;

    private ?int $maxResults = null;

    private int $uniqueParameterId = 0;

    /**
     * ProxyQuery constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * {@inheritDoc}
     */
    public function __call($name, $args)
    {
        return call_user_func_array([$this->query, $name], $args);
    }

    /**
     * Clones the object.
     */
    public function __clone()
    {
        $this->query = clone $this->query;
    }

    /**
     * Returns query.
     *
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(array $params = [], $hydrationMode = null): array
    {
        $query = clone $this->query;

        $sortBy = $this->getSortBy();

        if ($sortBy) {
            $query->orderBy($sortBy, $this->getSortOrder());
        }

        return $query->execute($hydrationMode);
    }

    /**
     * {@inheritDoc}
     */
    public function setSortBy($parentAssociationMappings, $fieldMapping): self
    {
        $this->sortBy = $fieldMapping['fieldName'];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    /**
     * {@inheritDoc}
     */
    public function setSortOrder($sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSortOrder(): ?string
    {
        return $this->sortOrder;
    }

    /**
     * {@inheritDoc}
     */
    public function getSingleScalarResult()
    {
        return $this->query->getSingleScalarResult();
    }

    /**
     * {@inheritDoc}
     */
    public function setFirstResult($firstResult): self
    {
        $this->firstResult = $firstResult;

        $this->query->offset($firstResult ?? 0);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    /**
     * {@inheritDoc}
     */
    public function setMaxResults($maxResults): self
    {
        $this->maxResults = $maxResults;

        $this->query->limit($maxResults ?? 0);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    /**
     * {@inheritDoc}
     */
    public function getUniqueParameterId(): int
    {
        return $this->uniqueParameterId++;
    }

    /**
     * {@inheritDoc}
     *
     * @throws BadMethodCallException
     */
    public function entityJoin(array $associationMappings): string
    {
        throw new BadMethodCallException(__METHOD__ . '() is not implemented yet.');
    }
}
