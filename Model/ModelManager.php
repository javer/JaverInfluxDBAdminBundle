<?php

namespace Javer\InfluxDB\AdminBundle\Model;

use DateTimeInterface;
use InvalidArgumentException;
use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQuery;
use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQueryInterface;
use Javer\InfluxDB\ODM\Mapping\ClassMetadata;
use Javer\InfluxDB\ODM\Mapping\MappingException;
use Javer\InfluxDB\ODM\MeasurementManager;
use Javer\InfluxDB\ODM\Query\Query;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Model\ProxyResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use TypeError;

final class ModelManager implements ModelManagerInterface, ProxyResolverInterface
{
    private const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    public function __construct(
        private readonly MeasurementManager $measurementManager,
        private readonly PropertyAccessorInterface $propertyAccessor,
    )
    {
    }

    public function getRealClass(object $object): string
    {
        $class = get_class($object);

        try {
            return $this->getMetadata($class)->getName();
        } catch (MappingException) {
            return $class;
        }
    }

    public function create(object $object): void
    {
        $this->measurementManager->persist($object);
    }

    public function update(object $object): void
    {
        $this->measurementManager->persist($object);
    }

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
     *
     * @throws TypeError
     */
    public function batchDelete(string $class, BaseProxyQueryInterface $query): void
    {
        if (!$query instanceof ProxyQueryInterface) {
            throw new TypeError(sprintf('The query MUST implement %s.', ProxyQueryInterface::class));
        }

        foreach ($query->getQuery()->getResult() as $object) {
            $this->measurementManager->remove($object);
        }
    }

    public function createQuery(string $class): ProxyQuery
    {
        return new ProxyQuery($this->measurementManager->getRepository($class)->createQuery());
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues(object $model): array
    {
        return array_map(
            static fn(mixed $value): string
                => (string) ($value instanceof DateTimeInterface ? $value->format(self::DATE_FORMAT) : $value),
            array_values($this->getMetadata(get_class($model))->getIdentifierValues($model)),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierFieldNames(string $class): array
    {
        return $this->getMetadata($class)->getIdentifierFieldNames();
    }

    public function getNormalizedIdentifier(object $model): ?string
    {
        if ($identifier = $this->getIdentifierValues($model)[0]) {
            return (string) $identifier;
        }

        return null;
    }

    public function getUrlSafeIdentifier(object $model): ?string
    {
        return $this->getNormalizedIdentifier($model);
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform(object $object, array $array = []): void
    {
        $metadata = $this->getMetadata(get_class($object));

        foreach ($array as $name => $value) {
            $property = $this->getFieldName($metadata, $name);

            $this->propertyAccessor->setValue($object, $property, $value);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function executeQuery(object $query): array
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
     * @throws TypeError
     * @throws InvalidArgumentException
     */
    public function addIdentifiersToQuery(string $class, BaseProxyQueryInterface $query, array $idx): void
    {
        if (!$query instanceof ProxyQueryInterface) {
            throw new TypeError(sprintf('The query MUST implement %s.', ProxyQueryInterface::class));
        }

        if (count($idx) !== 1) {
            throw new InvalidArgumentException(
                'InfluxDB does not support using OR in the WHERE clause to specify multiple time ranges'
            );
        }

        $queryBuilder = $query->getQuery();

        $classMetadata = $this->getMetadata($class);

        $queryBuilder->where($classMetadata->identifier, array_values($idx)[0]);
    }

    public function supportsQuery(object $query): bool
    {
        return $query instanceof Query;
    }

    private function getFieldName(ClassMetadata $metadata, string $name): string
    {
        if (array_key_exists($name, $metadata->fieldMappings)) {
            return $metadata->fieldMappings[$name]['fieldName'];
        }

        return $name;
    }

    /**
     * Returns metadata for the class.
     *
     * @phpstan-template T of object
     * @phpstan-param    class-string<T> $class
     * @phpstan-return   ClassMetadata<T>
     */
    private function getMetadata(string $class): ClassMetadata
    {
        return $this->measurementManager->getClassMetadata($class);
    }
}
