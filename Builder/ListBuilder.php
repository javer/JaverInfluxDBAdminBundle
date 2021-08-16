<?php

namespace Javer\InfluxDB\AdminBundle\Builder;

use RuntimeException;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;

final class ListBuilder implements ListBuilderInterface
{
    private TypeGuesserInterface $guesser;

    /**
     * @var string[]
     */
    private array $templates;

    /**
     * ListBuilder constructor.
     *
     * @param TypeGuesserInterface $guesser
     * @param string[]             $templates
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

    public function buildField(?string $type, FieldDescriptionInterface $fieldDescription): void
    {
        if ($type === null) {
            $guessType = $this->guesser->guess($fieldDescription);

            assert($guessType !== null);

            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($fieldDescription);
    }

    /**
     * {@inheritDoc}
     */
    public function addField(
        FieldDescriptionCollection $list,
        ?string $type,
        FieldDescriptionInterface $fieldDescription
    ): void
    {
        $this->buildField($type, $fieldDescription);

        $fieldDescription->getAdmin()->addListFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        if (
            $fieldDescription->getName() === ListMapper::NAME_ACTIONS
            || $fieldDescription->getType() === ListMapper::TYPE_ACTIONS
        ) {
            $this->buildActionFieldDescription($fieldDescription);
        }

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
                sprintf(
                    'Please define a type for field `%s` in `%s`',
                    $fieldDescription->getName(),
                    get_class($fieldDescription->getAdmin())
                )
            );
        }

        if (!$fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate($this->getTemplate($fieldDescription->getType()));
        }
    }

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

    private function getTemplate(string $type): ?string
    {
        return $this->templates[$type] ?? null;
    }
}
