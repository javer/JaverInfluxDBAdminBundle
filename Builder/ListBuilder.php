<?php

namespace Javer\InfluxDB\AdminBundle\Builder;

use RuntimeException;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;

/**
 * Class ListBuilder
 *
 * @package Javer\InfluxDB\AdminBundle\Builder
 */
class ListBuilder implements ListBuilderInterface
{
    private TypeGuesserInterface $guesser;

    private array $templates;

    /**
     * ListBuilder constructor.
     *
     * @param TypeGuesserInterface $guesser
     * @param array                $templates
     */
    public function __construct(TypeGuesserInterface $guesser, array $templates)
    {
        $this->guesser = $guesser;
        $this->templates = $templates;
    }

    /**
     * Returns base list.
     *
     * @param array $options
     *
     * @return FieldDescriptionCollection
     */
    public function getBaseList(array $options = []): FieldDescriptionCollection
    {
        return new FieldDescriptionCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function buildField($type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin): void
    {
        if (null === $type) {
            $guessType = $this->guesser
                ->guessType($admin->getClass(), $fieldDescription->getName(), $admin->getModelManager());

            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($admin, $fieldDescription);
    }

    /**
     * {@inheritDoc}
     */
    public function addField(
        FieldDescriptionCollection $list,
        $type,
        FieldDescriptionInterface $fieldDescription,
        AdminInterface $admin
    ): void
    {
        $this->buildField($type, $fieldDescription, $admin);

        $admin->addListFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription): void
    {
        if ($fieldDescription->getName() === '_action' || $fieldDescription->getType() === 'actions') {
            $this->buildActionFieldDescription($fieldDescription);
        }

        $fieldDescription->setAdmin($admin);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            [$metadata, $lastPropertyName, $parentAssociationMappings] = $admin->getModelManager()
                ->getParentMetadataForProperty($admin->getClass(), $fieldDescription->getName());
            $fieldDescription->setParentAssociationMappings($parentAssociationMappings);

            // set the default field mapping
            if (isset($metadata->fieldMappings[$lastPropertyName])) {
                $fieldMapping = $metadata->fieldMappings[$lastPropertyName];
                $fieldDescription->setFieldMapping($fieldMapping);

                if (($fieldMapping['id'] ?? false) !== true) {
                    // Only ORDER BY time supported at this time
                    $fieldDescription->setOption('sortable', false);
                }

                if ($fieldDescription->getOption('sortable') !== false) {
                    $fieldDescription->setOption('sortable', $fieldDescription->getOption('sortable', true));
                    $fieldDescription->setOption(
                        'sort_parent_association_mappings',
                        $fieldDescription->getOption(
                            'sort_parent_association_mappings',
                            $fieldDescription->getParentAssociationMappings()
                        )
                    );
                    $fieldDescription->setOption(
                        'sort_field_mapping',
                        $fieldDescription->getOption('sort_field_mapping', $fieldDescription->getFieldMapping())
                    );
                }
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$lastPropertyName])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$lastPropertyName]);
            }

            $fieldDescription->setOption('_sort_order', $fieldDescription->getOption('_sort_order', 'ASC'));
        }

        if (!$fieldDescription->getType()) {
            throw new RuntimeException(
                sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin))
            );
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (!$fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate($this->getTemplate($fieldDescription->getType()));
        }
    }

    /**
     * Build action field description.
     *
     * @param FieldDescriptionInterface $fieldDescription
     */
    private function buildActionFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        if ($fieldDescription->getTemplate() === null) {
            $fieldDescription->setTemplate('@SonataAdmin/CRUD/list__action.html.twig');
        }

        if ($fieldDescription->getType() === null) {
            $fieldDescription->setType('actions');
        }

        if ($fieldDescription->getOption('name') === null) {
            $fieldDescription->setOption('name', 'Action');
        }

        if ($fieldDescription->getOption('code') === null) {
            $fieldDescription->setOption('code', 'Action');
        }

        if ($fieldDescription->getOption('actions') !== null) {
            $actions = $fieldDescription->getOption('actions');

            foreach ($actions as $k => $action) {
                if (!isset($action['template'])) {
                    $actions[$k]['template'] = sprintf('@SonataAdmin/CRUD/list__action_%s.html.twig', $k);
                }
            }

            $fieldDescription->setOption('actions', $actions);
        }
    }

    /**
     * Returns template for the given type.
     *
     * @param string $type
     *
     * @return string|null
     */
    private function getTemplate(string $type): ?string
    {
        return $this->templates[$type] ?? null;
    }
}
