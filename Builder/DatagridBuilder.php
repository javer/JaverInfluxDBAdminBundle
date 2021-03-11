<?php

namespace Javer\InfluxDB\AdminBundle\Builder;

use Javer\InfluxDB\AdminBundle\Datagrid\Pager as InfluxDBPager;
use RuntimeException;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\SimplePager;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class DatagridBuilder
 *
 * @package Javer\InfluxDB\AdminBundle\Builder
 */
class DatagridBuilder implements DatagridBuilderInterface
{
    private FilterFactoryInterface $filterFactory;

    private FormFactoryInterface $formFactory;

    private TypeGuesserInterface $guesser;

    private bool $csrfTokenEnabled;

    /**
     * DatagridBuilder constructor.
     *
     * @param FormFactoryInterface   $formFactory
     * @param FilterFactoryInterface $filterFactory
     * @param TypeGuesserInterface   $guesser
     * @param boolean                $csrfTokenEnabled
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        FilterFactoryInterface $filterFactory,
        TypeGuesserInterface $guesser,
        bool $csrfTokenEnabled = true
    )
    {
        $this->formFactory = $formFactory;
        $this->filterFactory = $filterFactory;
        $this->guesser = $guesser;
        $this->csrfTokenEnabled = $csrfTokenEnabled;
    }

    /**
     * Fixes field description.
     *
     * @param AdminInterface            $admin
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @throws RuntimeException
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription): void
    {
        $fieldDescription->setAdmin($admin);

        if ($fieldDescription->getFieldMapping() !== []) {
            $fieldDescription->setOption(
                'field_mapping',
                $fieldDescription->getOption('field_mapping', $fieldDescription->getFieldMapping())
            );

            if ($fieldDescription->getFieldMapping()['type'] === 'string') {
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

    /**
     * {@inheritDoc}
     */
    public function addFilter(
        DatagridInterface $datagrid,
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
        AdminInterface $admin
    ): void
    {
        if ($type === null) {
            $guessType = $this->guesser
                ->guessType($admin->getClass(), $fieldDescription->getName(), $admin->getModelManager());

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

        $this->fixFieldDescription($admin, $fieldDescription);

        $admin->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);

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
     * @param string $pagerType
     *
     * @return Pager
     *
     * @throws RuntimeException
     */
    private function getPager(string $pagerType): Pager
    {
        switch ($pagerType) {
            case Pager::TYPE_DEFAULT:
                return new InfluxDBPager();

            case Pager::TYPE_SIMPLE:
                return new SimplePager();

            default:
                throw new RuntimeException(sprintf('Unknown pager type "%s".', $pagerType));
        }
    }
}
