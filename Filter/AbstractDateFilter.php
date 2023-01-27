<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use DateTime;
use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Sonata\AdminBundle\Form\Type\Operator\DateRangeOperatorType;

abstract class AbstractDateFilter extends Filter
{
    private const DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    /**
     * Flag indicating that filter will have range.
     */
    protected bool $range = false;

    /**
     * Flag indicating that filter will filter by datetime instead by date.
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
    public function getFormOptions(): array
    {
        return [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
            'operator_type' => $this->range ? DateRangeOperatorType::class : DateOperatorType::class,
        ];
    }

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

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
                    $value['start']->setTime(0, 0);
                }
                if ($value['end'] instanceof DateTime) {
                    $value['end']->setTime(23, 59, 59);
                }
            }

            $value['start'] = $value['start'] instanceof DateTime
                ? $value['start']
                : new DateTime(sprintf('@%d', (int) ($value['start'] ?? 0)));
            $value['end'] = $value['end'] instanceof DateTime
                ? $value['end']
                : new DateTime(sprintf('@%d', (int) ($value['end'] ?? time())));

            // default type for range filter
            $type = $data->getType() ?? DateRangeOperatorType::TYPE_BETWEEN;

            if ($type === DateRangeOperatorType::TYPE_NOT_BETWEEN) {
                $field = $query->getQuery()->getClassMetadata()->getFieldDatabaseName($field);
                $query->getQuery()->where(sprintf(
                    '(r.%s < time(v: "%s") or r.%s > time(v: "%s"))',
                    $field,
                    $value['start']->format(self::DATETIME_FORMAT),
                    $field,
                    $value['end']->format(self::DATETIME_FORMAT),
                ));
            } else {
                $this->applyRange($query, $value['start'], $value['end']);
            }
        } else {
            // default type for simple filter
            $type = $data->getType() ?? DateOperatorType::TYPE_EQUAL;

            // transform types
            $value = $value instanceof DateTime ? $value : new DateTime(sprintf('@%d', (int) $value));

            $this->setRange($type, $value, $query);
        }
    }

    private function setRange(int $type, DateTime $value, ProxyQueryInterface $query): void
    {
        $start = new DateTime('@0');
        $stop = new DateTime();

        switch ($type) {
            case DateOperatorType::TYPE_GREATER_EQUAL:
                $start = $value;
                break;
            case DateOperatorType::TYPE_GREATER_THAN:
                $start = $value->modify('+1µsec');
                break;
            case DateOperatorType::TYPE_EQUAL:
                if ($this->time) {
                    $start = $stop = $value;
                } else {
                    $start = $value;
                    $stop = (clone $value)->modify('+1day');
                }
                break;
            case DateOperatorType::TYPE_LESS_EQUAL:
                $stop = $value->modify('+1µsec');
                break;
            case DateOperatorType::TYPE_LESS_THAN:
                $stop = $value;
                break;
        }

        $this->applyRange($query, $start, $stop);
    }
}
