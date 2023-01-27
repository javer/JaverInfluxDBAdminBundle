<?php

namespace Javer\InfluxDB\AdminBundle\Builder;

use Javer\InfluxDB\AdminBundle\Datagrid\Pager as InfluxDBPager;
use Javer\InfluxDB\ODM\Types\TypeEnum;
use RuntimeException;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\SimplePager;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

final class DatagridBuilder implements DatagridBuilderInterface
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly FilterFactoryInterface $filterFactory,
        private readonly TypeGuesserInterface $guesser,
        private readonly bool $csrfTokenEnabled = true,
    )
    {
    }

    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        if ($fieldDescription->getFieldMapping() !== []) {
            $fieldDescription->setOption(
                'field_mapping',
                $fieldDescription->getOption('field_mapping', $fieldDescription->getFieldMapping())
            );

            if ($fieldDescription->getFieldMapping()['type'] === TypeEnum::STRING) {
                $fieldDescription->setOption('global_search', $fieldDescription->getOption('global_search', true));
            }
        }

        if ($fieldDescription->getAssociationMapping() !== []) {
            $fieldDescription->setOption(
                'association_mapping',
                $fieldDescription->getOption('association_mapping', $fieldDescription->getAssociationMapping())
            );
        }

        if ($fieldDescription->getParentAssociationMappings() !== []) {
            $fieldDescription->setOption(
                'parent_association_mappings',
                $fieldDescription->getOption(
                    'parent_association_mappings',
                    $fieldDescription->getParentAssociationMappings()
                )
            );
        }

        $fieldDescription->setOption(
            'field_name',
            $fieldDescription->getOption('field_name', $fieldDescription->getFieldName())
        );

        $fieldDescription->mergeOption('field_options', ['required' => false]);
    }

    public function addFilter(
        DatagridInterface $datagrid,
        ?string $type,
        FieldDescriptionInterface $fieldDescription
    ): void
    {
        if ($type === null) {
            $guessType = $this->guesser->guess($fieldDescription);

            assert($guessType !== null);

            $type = $guessType->getType();

            $fieldDescription->setType($type);

            $options = $guessType->getOptions();

            foreach ($options as $name => $value) {
                if (is_array($value)) {
                    $fieldDescription->setOption($name, array_merge($value, $fieldDescription->getOption($name, [])));
                } else {
                    $fieldDescription->setOption($name, $fieldDescription->getOption($name, $value));
                }
            }
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($fieldDescription);

        $fieldDescription->getAdmin()->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);

        $filter = $this->filterFactory->create($fieldDescription->getName(), $type, $fieldDescription->getOptions());

        $datagrid->addFilter($filter);
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseDatagrid(AdminInterface $admin, array $values = []): DatagridInterface
    {
        $pager = $this->getPager($admin->getPagerType());

        $defaultOptions = ['validation_groups' => false];

        if ($this->csrfTokenEnabled) {
            $defaultOptions['csrf_protection'] = false;
        }

        $formBuilder = $this->formFactory->createNamedBuilder('filter', FormType::class, [], $defaultOptions);

        return new Datagrid($admin->createQuery(), $admin->getList(), $pager, $formBuilder, $values);
    }

    /**
     * Get pager by pagerType.
     *
     * @throws RuntimeException
     */
    private function getPager(string $pagerType): Pager
    {
        return match ($pagerType) {
            Pager::TYPE_DEFAULT => new InfluxDBPager(),
            Pager::TYPE_SIMPLE => new SimplePager(),
            default => throw new RuntimeException(sprintf('Unknown pager type "%s".', $pagerType)),
        };
    }
}
