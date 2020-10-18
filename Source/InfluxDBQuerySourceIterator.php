<?php

namespace Javer\InfluxDB\AdminBundle\Source;

use Javer\InfluxDB\ODM\Query\Query;
use Sonata\Exporter\Exception\InvalidMethodCallException;
use Sonata\Exporter\Source\AbstractPropertySourceIterator;

/**
 * Class InfluxDBQuerySourceIterator
 *
 * @package Javer\InfluxDB\AdminBundle\Source
 */
class InfluxDBQuerySourceIterator extends AbstractPropertySourceIterator
{
    private Query $query;

    /**
     * InfluxDBQuerySourceIterator constructor.
     *
     * @param Query  $query
     * @param array  $fields
     * @param string $dateTimeFormat
     */
    public function __construct(Query $query, array $fields, string $dateTimeFormat = 'Y-m-d H:i:s.u')
    {
        $this->query = clone $query;

        parent::__construct($fields, $dateTimeFormat);
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidMethodCallException
     */
    public function rewind(): void
    {
        if ($this->iterator) {
            throw new InvalidMethodCallException('Cannot rewind a ' . Query::class);
        }

        $this->iterator = $this->query->iterate();
        $this->iterator->rewind();
    }
}
