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
    /**
     * Computes number of results.
     *
     * @return integer
     */
    public function computeNbResult(): int
    {
        /** @var Query $countQuery */
        $countQuery = clone $this->getQuery();

        return $countQuery->count('value')->getSingleScalarResult() ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getResults(): array
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
        $this->resetIterator();

        $this->setNbResults($this->computeNbResult());

        /** @var ProxyQuery $query */
        $query = $this->getQuery();

        $query->setFirstResult(0);
        $query->setMaxResults(0);

        if ($this->getPage() === 0 || $this->getMaxPerPage() === 0 || $this->getNbResults() === 0) {
            $this->setLastPage(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage((int) ceil($this->getNbResults() / $this->getMaxPerPage()));

            $query->setFirstResult($offset);
            $query->setMaxResults($this->getMaxPerPage());
        }
    }
}
