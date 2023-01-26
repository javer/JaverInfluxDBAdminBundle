<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\StringOperatorType;

class StringFilter extends Filter
{
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

        $field = $query->getQuery()->getClassMetadata()->getFieldDatabaseName($field);
        $type = $data->getType() ?? $this->getOption('operator_default_type');

        $condition = match ((int) $type) {
            StringOperatorType::TYPE_CONTAINS => sprintf('strings.containsStr(v: r.%s, substr: "%s")', $field, $value),
            StringOperatorType::TYPE_NOT_CONTAINS => sprintf(
                'not strings.containsStr(v: r.%s, substr: "%s")',
                $field,
                $value,
            ),
            StringOperatorType::TYPE_STARTS_WITH => sprintf('strings.hasPrefix(v: r.%s, prefix: "%s")', $field, $value),
            StringOperatorType::TYPE_ENDS_WITH => sprintf('strings.hasSuffix(v: r.%s, suffix: "%s")', $field, $value),
            StringOperatorType::TYPE_NOT_EQUAL => sprintf('r.%s != "%s"', $field, $value),
            default => sprintf('r.%s == "%s"', $field, $value),
        };

        $this->applyWhere($query, $condition);

        if (str_contains($condition, 'strings.')) {
            $query->getQuery()->addImport('strings');
        }
    }
}
