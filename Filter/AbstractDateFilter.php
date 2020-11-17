<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use DateInterval;
use DateTime;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeType;
use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Sonata\AdminBundle\Form\Type\Operator\DateRangeOperatorType;

/**
 * Class AbstractDateFilter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
abstract class AbstractDateFilter extends Filter
{
    protected const CHOICES = [
        DateOperatorType::TYPE_EQUAL => '=',
        DateOperatorType::TYPE_GREATER_EQUAL => '>=',
        DateOperatorType::TYPE_GREATER_THAN => '>',
        DateOperatorType::TYPE_LESS_EQUAL => '<=',
        DateOperatorType::TYPE_LESS_THAN => '<',
    ];

    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Flag indicating that filter will have range.
     *
     * @var boolean
     */
    protected bool $range = false;

    /**
     * Flag indicating that filter will filter by datetime instead by date.
     *
     * @var boolean
     */
    protected bool $time = false;

    /**
     * {@inheritDoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data): void
    {
        // check data sanity
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $field = $this->quoteFieldName($field);

        if ($this->range) {
            // additional data check for ranged items
            if (!array_key_exists('start', $data['value']) || !array_key_exists('end', $data['value'])) {
                return;
            }

            if (!$data['value']['start'] && !$data['value']['end']) {
                return;
            }

            // date filter should filter records for the whole days
            if (false === $this->time) {
                if ($data['value']['start'] instanceof DateTime) {
                    $data['value']['start']->setTime(0, 0, 0);
                }
                if ($data['value']['end'] instanceof DateTime) {
                    $data['value']['end']->setTime(23, 59, 59);
                }
            }

            // transform types
            if ('timestamp' === $this->getOption('input_type')) {
                $data['value']['start'] = $data['value']['start'] instanceof DateTime
                    ? $data['value']['start']->getTimestamp()
                    : 0;
                $data['value']['end'] = $data['value']['end'] instanceof DateTime
                    ? $data['value']['end']->getTimestamp()
                    : 0;
            }

            $fromDate = date(self::DATETIME_FORMAT, (int) $data['value']['start']);
            $toDate = date(self::DATETIME_FORMAT, (int) $data['value']['end']);

            // default type for range filter
            $data['type'] = !isset($data['type']) || !is_numeric($data['type'])
                ? DateRangeOperatorType::TYPE_BETWEEN
                : $data['type'];

            if (DateRangeOperatorType::TYPE_NOT_BETWEEN === $data['type']) {
                $this->applyWhere($queryBuilder, sprintf("%s < '%s' OR %s > '%s'", $field, $fromDate, $field, $toDate));
            } else {
                if ($data['value']['start']) {
                    $this->applyWhere($queryBuilder, sprintf("%s >= '%s'", $field, $fromDate));
                }

                if ($data['value']['end']) {
                    $this->applyWhere($queryBuilder, sprintf("%s <= '%s'", $field, $toDate));
                }
            }
        } else {
            if (!$data['value']) {
                return;
            }

            // default type for simple filter
            $data['type'] = !isset($data['type']) || !is_numeric($data['type'])
                ? DateOperatorType::TYPE_EQUAL
                : $data['type'];

            // just find an operator and apply query
            $operator = $this->getOperator($data['type']);

            // transform types
            if ('timestamp' === $this->getOption('input_type')) {
                $data['value'] = $data['value'] instanceof DateTime ? $data['value']->getTimestamp() : 0;
            }

            if (in_array($operator, ['NULL', 'NOT NULL'], true)) {
                return;
            }

            $fromDate = date(self::DATETIME_FORMAT, (int) $data['value']);

            // date filter should filter records for the whole day
            if (false === $this->time && DateOperatorType::TYPE_EQUAL === $data['type']) {
                $this->applyWhere($queryBuilder, sprintf("%s %s '%s'", $field, '>=', $fromDate));

                if ('timestamp' === $this->getOption('input_type')) {
                    $endValue = strtotime('+1 day', $data['value']);
                } else {
                    $endValue = clone $data['value'];
                    $endValue->add(new DateInterval('P1D'));
                }

                $toDate = date(self::DATETIME_FORMAT, (int) $endValue);

                $this->applyWhere($queryBuilder, sprintf("%s %s '%s'", $field, '<', $toDate));

                return;
            }

            $this->applyWhere($queryBuilder, sprintf("%s %s '%s'", $field, $operator, $fromDate));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'input_type' => 'timestamp',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderSettings(): array
    {
        $name = DateType::class;

        if ($this->time && $this->range) {
            $name = DateTimeRangeType::class;
        } elseif ($this->time) {
            $name = DateTimeType::class;
        } elseif ($this->range) {
            $name = DateRangeType::class;
        }

        return [
            $name,
            [
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'label' => $this->getLabel(),
            ],
        ];
    }

    /**
     * Returns operator for the given type.
     *
     * @param integer $type
     *
     * @return string
     */
    protected function getOperator(int $type): string
    {
        return self::CHOICES[$type] ?? self::CHOICES[DateOperatorType::TYPE_EQUAL];
    }
}
