<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use DateInterval;
use DateTime;
use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeType;
use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Sonata\AdminBundle\Form\Type\Operator\DateRangeOperatorType;

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

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

        $field = $this->quoteFieldName($field);
        $value = $data->getValue();

        if ($this->range) {
            // additional data check for ranged items
            if (
                !is_array($value)
                || !array_key_exists('start', $value)
                || !array_key_exists('end', $value)
            ) {
                return;
            }

            if (!$value['start'] && !$value['end']) {
                return;
            }

            // date filter should filter records for the whole days
            if ($this->time === false) {
                if ($value['start'] instanceof DateTime) {
                    $value['start']->setTime(0, 0, 0);
                }
                if ($value['end'] instanceof DateTime) {
                    $value['end']->setTime(23, 59, 59);
                }
            }

            // transform types
            if ($this->getOption('input_type') === 'timestamp') {
                $value['start'] = $value['start'] instanceof DateTime
                    ? $value['start']->getTimestamp()
                    : 0;
                $value['end'] = $value['end'] instanceof DateTime
                    ? $value['end']->getTimestamp()
                    : 0;
            }

            $fromDate = date(self::DATETIME_FORMAT, (int) $value['start']);
            $toDate = date(self::DATETIME_FORMAT, (int) $value['end']);

            // default type for range filter
            $type = $data->getType() ?? DateRangeOperatorType::TYPE_BETWEEN;

            if ($type === DateRangeOperatorType::TYPE_NOT_BETWEEN) {
                $this->applyWhere($query, sprintf("%s < '%s' OR %s > '%s'", $field, $fromDate, $field, $toDate));
            } else {
                if ($value['start']) {
                    $this->applyWhere($query, sprintf("%s >= '%s'", $field, $fromDate));
                }

                if ($value['end']) {
                    $this->applyWhere($query, sprintf("%s <= '%s'", $field, $toDate));
                }
            }
        } else {
            // default type for simple filter
            $type = $data->getType() ?? DateOperatorType::TYPE_EQUAL;

            // just find an operator and apply query
            $operator = $this->getOperator($type);

            // transform types
            if ($this->getOption('input_type') === 'timestamp') {
                $value = $value instanceof DateTime ? $value->getTimestamp() : 0;
            }

            if (in_array($operator, ['NULL', 'NOT NULL'], true)) {
                return;
            }

            $fromDate = date(self::DATETIME_FORMAT, (int) $value);

            // date filter should filter records for the whole day
            if ($this->time === false && $type === DateOperatorType::TYPE_EQUAL) {
                $this->applyWhere($query, sprintf("%s %s '%s'", $field, '>=', $fromDate));

                if ($this->getOption('input_type') === 'timestamp') {
                    $endValue = strtotime('+1 day', $value);
                } else {
                    $endValue = clone $value;
                    $endValue->add(new DateInterval('P1D'));
                }

                $toDate = date(self::DATETIME_FORMAT, (int) $endValue);

                $this->applyWhere($query, sprintf("%s %s '%s'", $field, '<', $toDate));

                return;
            }

            $this->applyWhere($query, sprintf("%s %s '%s'", $field, $operator, $fromDate));
        }
    }

    protected function getOperator(int $type): string
    {
        return self::CHOICES[$type] ?? self::CHOICES[DateOperatorType::TYPE_EQUAL];
    }
}
