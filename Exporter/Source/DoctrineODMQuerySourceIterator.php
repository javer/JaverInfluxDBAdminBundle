<?php

namespace Javer\InfluxDB\AdminBundle\Exporter\Source;

use Javer\InfluxDB\ODM\Query\Query;
use Sonata\Exporter\Source\AbstractPropertySourceIterator;

final class DoctrineODMQuerySourceIterator extends AbstractPropertySourceIterator
{
    private readonly Query $query;

    /**
     * Constructor.
     *
     * @param Query    $query
     * @param string[] $fields
     * @param string   $dateTimeFormat
     */
    public function __construct(Query $query, array $fields, string $dateTimeFormat = 'r')
    {
        $this->query = clone $query;

        parent::__construct($fields, $dateTimeFormat);
    }

    public function rewind(): void
    {
        if ($this->iterator === null) {
            $this->iterator = $this->query->getIterator();
        }

        $this->iterator->rewind();
    }
}
