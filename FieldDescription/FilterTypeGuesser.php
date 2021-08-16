<?php

namespace Javer\InfluxDB\AdminBundle\FieldDescription;

use Javer\InfluxDB\AdminBundle\Filter\BooleanFilter;
use Javer\InfluxDB\AdminBundle\Filter\DateTimeFilter;
use Javer\InfluxDB\AdminBundle\Filter\NumberFilter;
use Javer\InfluxDB\AdminBundle\Filter\StringFilter;
use Javer\InfluxDB\AdminBundle\Model\MissingPropertyMetadataException;
use Javer\InfluxDB\ODM\Types\Type;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class FilterTypeGuesser implements TypeGuesserInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws MissingPropertyMetadataException
     */
    public function guess(FieldDescriptionInterface $fieldDescription): TypeGuess
    {
        $options = [
            'parent_association_mappings' => $fieldDescription->getParentAssociationMappings(),
            'field_name' => $fieldDescription->getFieldName(),
            'field_type' => null,
            'field_options' => [],
            'options' => [],
        ];

        if ([] === $fieldDescription->getFieldMapping()) {
            throw new MissingPropertyMetadataException(
                $fieldDescription->getAdmin()->getClass(),
                $fieldDescription->getFieldName()
            );
        }

        switch ($fieldDescription->getMappingType()) {
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
