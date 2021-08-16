<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class BooleanFilter extends Filter
{
    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'field_type' => BooleanType::class,
            'operator_type' => HiddenType::class,
            'operator_options' => [],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderSettings(): array
    {
        return [
            DefaultType::class,
            [
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'operator_type' => $this->getOption('operator_type'),
                'operator_options' => $this->getOption('operator_options'),
                'label' => $this->getLabel(),
            ],
        ];
    }

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

        $value = $data->getValue();

        if (!in_array($value, [BooleanType::TYPE_NO, BooleanType::TYPE_YES], true)) {
            return;
        }

        $field = $this->quoteFieldName($field);
        $value = $this->quoteFieldValue($value === BooleanType::TYPE_YES ? 1 : 0);

        $this->applyWhere($query, sprintf('%s = %s', $field, $value));
    }
}
