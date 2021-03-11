<?php

namespace Javer\InfluxDB\AdminBundle\Datagrid;

use Javer\InfluxDB\ODM\Query\Query;
use Sonata\AdminBundle\Datagrid\Pager as BasePager;

/**
 * Class Pager
 *
 * @package Javer\InfluxDB\AdminBundle\Datagrid
 */
class Pager extends BasePager
{
    private int $resultsCount = 0;

    /**
     * Computes count of results.
     *
     * @return integer
     */
    public function countResults(): int
    {
        return $this->resultsCount;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentPageResults(): array
    {
        /** @var ProxyQuery $query */
        $query = $this->getQuery();

        if ($query->getMaxResults() === 0) {
            return [];
        }

        return $query->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function init(): void
    {
        $this->resultsCount = $this->computeResultsCount();

        /** @var ProxyQuery $query */
        $query = $this->getQuery();

        $query->setFirstResult(0);
        $query->setMaxResults(0);

        if ($this->getPage() === 0 || $this->getMaxPerPage() === 0 || $this->countResults() === 0) {
            $this->setLastPage(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage((int) ceil($this->countResults() / $this->getMaxPerPage()));

            $query->setFirstResult($offset);
            $query->setMaxResults($this->getMaxPerPage());
        }
    }

    /**
     * Computes count of results.
     *
     * @return integer
     */
    private function computeResultsCount(): int
    {
        /** @var Query $countQuery */
        $countQuery = clone $this->getQuery();

        return $countQuery->executeCount();
    }
}
