<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Content;

use JMS\Serializer\Annotation as Serializer;
use Pixel\DirectoryBundle\Entity\Card;
use Sulu\Component\SmartContent\ItemInterface;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class CardDataItem implements ItemInterface
{
    private Card $entity;

    public function __construct(Card $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getId(): string
    {
        return (string) $this->entity->getId();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getTitle(): string
    {
        return (string) $this->entity->getName();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getImage(): ?string
    {
        return null;
    }

    public function getResource(): Card
    {
        return $this->entity;
    }
}
