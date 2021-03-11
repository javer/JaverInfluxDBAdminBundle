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
    public function buildField(?string $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin): void
    {
        if ($type === null) {
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
        ?string $type,
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

        if (($fieldMapping = $fieldDescription->getFieldMapping()) !== []) {
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

            $fieldDescription->setOption('_sort_order', $fieldDescription->getOption('_sort_order', 'ASC'));
        }

        if (!$fieldDescription->getType()) {
            throw new RuntimeException(
                sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin))
            );
        }

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

        if (in_array($fieldDescription->getType(), [null, '_action'], true)) {
            $fieldDescription->setType('actions');
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
