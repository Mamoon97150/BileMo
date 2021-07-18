<?php

namespace App\Service;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

class ExistingObjectConstructor implements ObjectConstructorInterface
{
    //TODO: delete ?
    public const ATTRIBUTE = 'target';

    private ObjectConstructorInterface $fallbackConstructor;

    /**
     * ExistingObjectConstructor constructor.
     * @param ObjectConstructorInterface $fallbackConstructor
     */
    public function __construct(ObjectConstructorInterface $fallbackConstructor)
    {
        $this->fallbackConstructor = $fallbackConstructor;
    }

    /**
     * @inheritDoc
     */
    public function construct(DeserializationVisitorInterface $visitor, ClassMetadata $metadata, $data, array $type, DeserializationContext $context): ?object
    {
        if ($context->hasAttribute(self::ATTRIBUTE)) {
            return $context->getAttribute(self::ATTRIBUTE);
        }

        return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
    }
}