<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Operator\StringOperatorType;

class StringFilter extends Filter
{
    private const CHOICES = [
        StringOperatorType::TYPE_CONTAINS => '=~',
        StringOperatorType::TYPE_STARTS_WITH => '=~',
        StringOperatorType::TYPE_ENDS_WITH => '=~',
        StringOperatorType::TYPE_NOT_CONTAINS => '!~',
        StringOperatorType::TYPE_EQUAL => '=',
    ];

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'operator_type' => StringOperatorType::class,
            'operator_options' => [],
            'operator_default_type' => StringOperatorType::TYPE_CONTAINS,
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
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
        ];
    }

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

        $value = trim((string) ($data->getValue() ?? ''));

        if ($value === '') {
            return;
        }

        $field = $this->quoteFieldName($field);
        $type = $data->getType() ?? $this->getOption('operator_default_type');
        $operator = $this->getOperator((int) $type);
        $format = "'%s'";

        if (in_array($type, [StringOperatorType::TYPE_CONTAINS, StringOperatorType::TYPE_NOT_CONTAINS], true)) {
            $format = '/%s/';
        } elseif ($type === StringOperatorType::TYPE_STARTS_WITH) {
            $format = '/^%s/';
        } elseif ($type === StringOperatorType::TYPE_ENDS_WITH) {
            $format = '/%s$/';
        }

        $value = $type === StringOperatorType::TYPE_EQUAL ? $value : preg_quote($value, '/');

        $this->applyWhere($query, sprintf('%s %s ' . $format, $field, $operator, $value));
    }

    private function getOperator(int $type): string
    {
        return self::CHOICES[$type] ?? self::CHOICES[StringOperatorType::TYPE_EQUAL];
    }
}
