<?php

namespace Javer\InfluxDB\AdminBundle\Guesser;

use Javer\InfluxDB\AdminBundle\Filter\BooleanFilter;
use Javer\InfluxDB\AdminBundle\Filter\DateTimeFilter;
use Javer\InfluxDB\AdminBundle\Filter\NumberFilter;
use Javer\InfluxDB\AdminBundle\Filter\StringFilter;
use Javer\InfluxDB\AdminBundle\Model\MissingPropertyMetadataException;
use Javer\InfluxDB\ODM\Types\Type;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * Class FilterTypeGuesser
 *
 * @package Javer\InfluxDB\AdminBundle\Guesser
 */
class FilterTypeGuesser extends AbstractTypeGuesser
{
    /**
     * {@inheritDoc}
     *
     * @throws MissingPropertyMetadataException
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager): ?TypeGuess
    {
        if (!$ret = $this->getParentMetadataForProperty($class, $property, $modelManager)) {
            return null;
        }

        $options = [
            'field_type' => null,
            'field_options' => [],
            'options' => [],
        ];

        [$metadata, $propertyName, $parentAssociationMappings] = $ret;

        $options['parent_association_mappings'] = $parentAssociationMappings;

        if (!array_key_exists($propertyName, $metadata->fieldMappings)) {
            throw new MissingPropertyMetadataException($class, $property);
        }

        $options['field_name'] = $metadata->fieldMappings[$propertyName]['fieldName'];

        switch ($metadata->getTypeOfField($propertyName)) {
            case Type::TIMESTAMP:
                $options['field_type'] = DateTimeType::class;
                return new TypeGuess(DateTimeFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case Type::BOOLEAN:
                $options['field_type'] = BooleanType::class;
                $options['field_options'] = [];
                return new TypeGuess(BooleanFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case Type::INTEGER:
            case Type::FLOAT:
                $options['field_type'] = NumberType::class;
                return new TypeGuess(NumberFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case Type::STRING:
                $options['field_type'] = TextType::class;
                return new TypeGuess(StringFilter::class, $options, Guess::MEDIUM_CONFIDENCE);
            default:
                return new TypeGuess(StringFilter::class, $options, Guess::LOW_CONFIDENCE);
        }
    }
}
