<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Domain\Event;

use Pixel\DirectoryBundle\Entity\Card;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class CardModifiedEvent extends DomainEvent
{
    private Card $card;

    /**
     * @var array<mixed>
     */
    private array $payload;

    /**
     * @param array<mixed> $payload
     */
    public function __construct(Card $card, array $payload)
    {
        parent::__construct();
        $this->card = $card;
        $this->payload = $payload;
    }

    public function getCard(): Card
    {
        return $this->card;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getEventType(): string
    {
        return 'modified';
    }

    public function getResourceKey(): string
    {
        return Card::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string) $this->card->getId();
    }

    public function getResourceTitle(): ?string
    {
        return $this->card->getName();
    }

    public function getResourceSecurityContext(): ?string
    {
        return Card::SECURITY_CONTEXT;
    }
}
