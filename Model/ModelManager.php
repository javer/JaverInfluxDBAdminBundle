<?php

namespace Javer\InfluxDB\AdminBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Javer\InfluxDB\AdminBundle\Admin\FieldDescription;
use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQuery;
use Javer\InfluxDB\AdminBundle\Source\InfluxDBQuerySourceIterator;
use Javer\InfluxDB\ODM\Mapping\ClassMetadata;
use Javer\InfluxDB\ODM\MeasurementManager;
use Javer\InfluxDB\ODM\Query\Query;
use Javer\InfluxDB\ODM\Types\Type;
use RuntimeException;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 * Class ModelManager
 *
 * @package Javer\InfluxDB\AdminBundle\Model
 */
class ModelManager implements ModelManagerInterface
{
    public const ID_SEPARATOR = '-';

    private MeasurementManager $measurementManager;

    /**
     * ModelManager constructor.
     *
     * @param MeasurementManager $measurementManager
     */
    public function __construct(MeasurementManager $measurementManager)
    {
        $this->measurementManager = $measurementManager;
    }

    /**
     * Returns metadata for the given class.
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function getMetadata(string $class): ClassMetadata
    {
        return $this->measurementManager->getClassMetadata($class);
    }

    /**
     * Returns the model's metadata holding the fully qualified property, and the last property name.
     *
     * @param string $baseClass        The base class of the model holding the fully qualified property
     * @param string $propertyFullName The name of the fully qualified property (dot separated property string)
     *
     * @return array
     */
    public function getParentMetadataForProperty(string $baseClass, string $propertyFullName): array
    {
        $nameElements = explode('.', $propertyFullName);
        $lastPropertyName = array_pop($nameElements);
        $class = $baseClass;
        $parentAssociationMappings = [];

        foreach ($nameElements as $nameElement) {
            $metadata = $this->getMetadata($class);
            $class = $metadata->getAssociationTargetClass($nameElement);
        }

        return [$this->getMetadata($class), $lastPropertyName, $parentAssociationMappings];
    }

    /**
     * Checks whether we have metadata for the given class.
     *
     * @param string $class
     *
     * @return boolean
     */
    public function hasMetadata(string $class): bool
    {
        return $this->measurementManager->getMetadataFactory()->hasMetadataFor($class);
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = []): FieldDescription
    {
        if (!is_string($name)) {
            throw new RuntimeException('The name argument must be a string');
        }

        $options['route']['name'] ??= 'edit';
        $options['route']['parameters'] ??= [];

        [$metadata, $propertyName] = $this->getParentMetadataForProperty($class, $name);

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);

        if (isset($metadata->fieldMappings[$propertyName])) {
            $fieldDescription->setFieldMapping($metadata->fieldMappings[$propertyName]);
        }

        return $fieldDescription;
    }

    /**
     * {@inheritDoc}
     */
    public function create($object): void
    {
        $this->measurementManager->persist($object);
    }

    /**
     * {@inheritDoc}
     */
    public function update($object): void
    {
        $this->measurementManager->persist($object);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($object): void
    {
        $this->measurementManager->remove($object);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy($class, array $criteria = []): array
    {
        return $this->measurementManager->getRepository($class)->findBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy($class, array $criteria = []): ?object
    {
        return $this->measurementManager->getRepository($class)->findOneBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function find($class, $id): ?object
    {
        return $this->measurementManager->getRepository($class)->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy): void
    {
        /** @var Query $query */
        $query = $queryProxy->getQuery();

        foreach ($query->getResult() as $object) {
            $this->measurementManager->remove($object);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getParentFieldDescription($parentAssociationMapping, $class): FieldDescription
    {
        return $this->getNewFieldDescriptionInstance($class, $parentAssociationMapping['fieldName']);
    }

    /**
     * {@inheritDoc}
     */
    public function createQuery($class, $alias = 'o'): ProxyQuery
    {
        return new ProxyQuery($this->measurementManager->getRepository($class)->createQuery());
    }

    /**
     * {@inheritDoc}
     */
    public function getModelIdentifier($class): string
    {
        return $this->getMetadata($class)->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues($model): array
    {
        $class = get_class($model);
        $metadata = $this->getMetadata($class);

        $identifiers = [];

        foreach ($metadata->getIdentifierValues($model) as $name => $value) {
            if (!is_object($value)) {
                $identifiers[] = $value;

                continue;
            }

            $fieldType = $metadata->getTypeOfField($name);

            $identifiers[] = Type::getType($fieldType)->convertToDatabaseValue($value);
        }

        return $identifiers;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierFieldNames($class): array
    {
        return $this->getMetadata($class)->getIdentifierFieldNames();
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function getNormalizedIdentifier($model): ?string
    {
        if ($model === null) {
            return null;
        }

        if (!is_object($model)) {
            throw new RuntimeException('Invalid argument, object or null required');
        }

        $values = $this->getIdentifierValues($model);

        return implode(self::ID_SEPARATOR, $values);
    }

    /**
     * {@inheritDoc}
     */
    public function getUrlSafeIdentifier($model): ?string
    {
        return $this->getNormalizedIdentifier($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelInstance($class): object
    {
        return $this->getMetadata($class)->newInstance();
    }

    /**
     * {@inheritDoc}
     */
    public function getModelCollectionInstance($class): ArrayCollection
    {
        return new ArrayCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function collectionRemoveElement(&$collection, &$element)
    {
        return $collection->removeElement($element);
    }

    /**
     * {@inheritDoc}
     */
    public function collectionAddElement(&$collection, &$element)
    {
        return $collection->add($element);
    }

    /**
     * {@inheritDoc}
     */
    public function collectionHasElement(&$collection, &$element): bool
    {
        return $collection->contains($element);
    }

    /**
     * {@inheritDoc}
     */
    public function collectionClear(&$collection)
    {
        return $collection->clear();
    }

    /**
     * {@inheritDoc}
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid): array
    {
        $values = $datagrid->getValues();

        if ($values['_sort_order'] === 'ASC' && $this->isFieldAlreadySorted($fieldDescription, $datagrid)) {
            $values['_sort_order'] = 'DESC';
        } else {
            $values['_sort_order'] = 'ASC';
        }

        $values['_sort_by'] = is_string($fieldDescription->getOption('sortable'))
            ? $fieldDescription->getOption('sortable')
            : $fieldDescription->getName();

        return ['filter' => $values];
    }

    /**
     * {@inheritDoc}
     */
    public function modelReverseTransform($class, array $array = []): object
    {
        $hydrator = $this->measurementManager->createHydrator($class);

        return $hydrator->hydrate($array);
    }

    /**
     * {@inheritDoc}
     */
    public function modelTransform($class, $instance): object
    {
        return $instance;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function executeQuery($query)
    {
        if (!$query instanceof ProxyQuery) {
            throw new InvalidArgumentException('Query must be ' . ProxyQuery::class);
        }

        return $query->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function getDataSourceIterator(
        DatagridInterface $datagrid,
        array $fields,
        $firstResult = null,
        $maxResult = null
    )
    {
        $datagrid->buildPager();
        $query = $datagrid->getQuery();

        $query->setFirstResult($firstResult);
        $query->setMaxResults($maxResult);

        return new InfluxDBQuerySourceIterator(
            $query instanceof ProxyQuery ? $query->getQuery() : $query,
            $fields
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getExportFields($class): array
    {
        return $this->getMetadata($class)->getFieldNames();
    }

    /**
     * {@inheritDoc}
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page): array
    {
        $values = $datagrid->getValues();

        if (isset($values['_sort_by']) && $values['_sort_by'] instanceof FieldDescriptionInterface) {
            $values['_sort_by'] = $values['_sort_by']->getName();
        }
        $values['_page'] = $page;

        return ['filter' => $values];
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx): void
    {
        if (count($idx) !== 1) {
            throw new InvalidArgumentException(
                'InfluxDB does not support using OR in the WHERE clause to specify multiple time ranges'
            );
        }

        /** @var Query $queryBuilder */
        $queryBuilder = $query->getQuery();

        $classMetadata = $this->getMetadata($class);

        $queryBuilder->where($classMetadata->identifier, array_values($idx)[0]);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultSortValues($class): array
    {
        return [
            '_sort_order' => 'ASC',
            '_sort_by' => $this->getModelIdentifier($class),
            '_page' => 1,
            '_per_page' => 25,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultPerPageOptions(string $class): array
    {
        return [10, 25, 50, 100, 250];
    }

    /**
     * Checks whether field is already sorted.
     *
     * @param FieldDescriptionInterface $fieldDescription
     * @param DatagridInterface         $datagrid
     *
     * @return boolean
     */
    private function isFieldAlreadySorted(
        FieldDescriptionInterface $fieldDescription,
        DatagridInterface $datagrid
    ): bool
    {
        $values = $datagrid->getValues();

        if (!isset($values['_sort_by']) || !$values['_sort_by'] instanceof FieldDescriptionInterface) {
            return false;
        }

        return $values['_sort_by']->getName() === $fieldDescription->getName()
            || $values['_sort_by']->getName() === $fieldDescription->getOption('sortable');
    }
}
