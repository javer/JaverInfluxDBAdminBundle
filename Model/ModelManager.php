<?php

namespace Javer\InfluxDB\AdminBundle\Model;

use InvalidArgumentException;
use Javer\InfluxDB\AdminBundle\Admin\FieldDescription;
use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQuery;
use Javer\InfluxDB\ODM\Mapping\ClassMetadata;
use Javer\InfluxDB\ODM\MeasurementManager;
use Javer\InfluxDB\ODM\Query\Query;
use Javer\InfluxDB\ODM\Types\Type;
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
     * {@inheritDoc}
     */
    public function getNewFieldDescriptionInstance(string $class, string $name, array $options = []): FieldDescription
    {
        $options['route']['name'] ??= 'edit';
        $options['route']['parameters'] ??= [];

        [$metadata, $propertyName] = $this->getParentMetadataForProperty($class, $name);

        return new FieldDescription(
            $name,
            $options,
            $metadata->fieldMappings[$propertyName] ?? [],
            [],
            [],
            $propertyName
        );
    }

    /**
     * {@inheritDoc}
     */
    public function create(object $object): void
    {
        $this->measurementManager->persist($object);
    }

    /**
     * {@inheritDoc}
     */
    public function update(object $object): void
    {
        $this->measurementManager->persist($object);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(object $object): void
    {
        $this->measurementManager->remove($object);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(string $class, array $criteria = []): array
    {
        return $this->measurementManager->getRepository($class)->findBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(string $class, array $criteria = []): ?object
    {
        return $this->measurementManager->getRepository($class)->findOneBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function find(string $class, $id): ?object
    {
        return $this->measurementManager->getRepository($class)->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function batchDelete(string $class, ProxyQueryInterface $queryProxy): void
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
    public function createQuery(string $class): ProxyQuery
    {
        return new ProxyQuery($this->measurementManager->getRepository($class)->createQuery());
    }

    /**
     * {@inheritDoc}
     */
    public function getModelIdentifier(string $class): string
    {
        return $this->getMetadata($class)->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues(object $model): array
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
    public function getIdentifierFieldNames(string $class): array
    {
        return $this->getMetadata($class)->getIdentifierFieldNames();
    }

    /**
     * {@inheritDoc}
     */
    public function getNormalizedIdentifier(object $model): ?string
    {
        $values = $this->getIdentifierValues($model);

        return implode(self::ID_SEPARATOR, $values);
    }

    /**
     * {@inheritDoc}
     */
    public function getUrlSafeIdentifier(object $model): ?string
    {
        return $this->getNormalizedIdentifier($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelInstance(string $class): object
    {
        return $this->getMetadata($class)->newInstance();
    }

    /**
     * {@inheritDoc}
     */
    public function modelReverseTransform(string $class, array $array = []): object
    {
        $hydrator = $this->measurementManager->createHydrator($class);

        return $hydrator->hydrate($array);
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function executeQuery(object $query)
    {
        if (!$query instanceof ProxyQuery) {
            throw new InvalidArgumentException('Query must be ' . ProxyQuery::class);
        }

        return $query->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function getExportFields(string $class): array
    {
        return $this->getMetadata($class)->getFieldNames();
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function addIdentifiersToQuery(string $class, ProxyQueryInterface $query, array $idx): void
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
    public function supportsQuery(object $query): bool
    {
        return $query instanceof Query;
    }

    /**
     * Returns metadata for the given class.
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    private function getMetadata(string $class): ClassMetadata
    {
        return $this->measurementManager->getClassMetadata($class);
    }
}
