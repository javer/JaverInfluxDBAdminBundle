<?php

namespace Javer\InfluxDB\AdminBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\Pager as BasePager;

final class Pager extends BasePager
{
    private int $resultsCount = 0;

    public function countResults(): int
    {
        return $this->resultsCount;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentPageResults(): array
    {
        $query = $this->getQuery();

        if (!$query?->getMaxResults()) {
            return [];
        }

        return $query->execute();
    }

    public function init(): void
    {
        $this->resultsCount = $this->computeResultsCount();

        $query = $this->getQuery();

        if ($query === null) {
            return;
        }

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

    private function computeResultsCount(): int
    {
        $query = clone $this->getQuery();

        assert($query instanceof ProxyQueryInterface);

        return $query->getQuery()->count()->getSingleScalarResult();
    }
}
