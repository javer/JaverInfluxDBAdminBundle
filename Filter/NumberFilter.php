<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;
use Symfony\Component\Form\Extension\Core\Type\NumberType as FormNumberType;

class NumberFilter extends Filter
{
    private const CHOICES = [
        NumberOperatorType::TYPE_EQUAL => '=',
        NumberOperatorType::TYPE_GREATER_EQUAL => '>=',
        NumberOperatorType::TYPE_GREATER_THAN => '>',
        NumberOperatorType::TYPE_LESS_EQUAL => '<=',
        NumberOperatorType::TYPE_LESS_THAN => '<',
    ];

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'field_type' => FormNumberType::class,
            'operator_type' => NumberOperatorType::class,
            'operator_options' => [],
            'operator_default_type' => NumberOperatorType::TYPE_EQUAL,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderSettings(): array
    {
        return [
            NumberType::class,
            [
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'label' => $this->getLabel(),
            ],
        ];
    }

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue() || !is_numeric($data->getValue())) {
            return;
        }

        $field = $this->quoteFieldName($field);
        $type = $data->getType() ?? $this->getOption('operator_default_type');
        $operator = $this->getOperator((int) $type);
        $value = $this->quoteFieldValue($data->getValue());

        $this->applyWhere($query, sprintf("%s %s %s", $field, $operator, $value));
    }

    private function getOperator(int $type): string
    {
        return self::CHOICES[$type] ?? self::CHOICES[NumberOperatorType::TYPE_EQUAL];
    }
}
