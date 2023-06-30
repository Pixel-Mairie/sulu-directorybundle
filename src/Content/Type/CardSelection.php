<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Content\Type;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\DirectoryBundle\Entity\Card;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class CardSelection extends SimpleContentType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct('card_selection', []);
    }

    /**
     * @return Card[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();

        if (empty($ids)) {
            return [];
        }

        $cards = $this->entityManager->getRepository(Card::class)->findBy([
            'id' => $ids,
        ]);

        $idPositions = array_flip($ids);
        usort($cards, function (Card $a, Card $b) use ($idPositions) {
            return $idPositions[$a->getId()] - $idPositions[$b->getId()];
        });

        return $cards;
    }

    /**
     * @return array<string, array<int>|null>
     */
    public function getViewData(PropertyInterface $property): array
    {
        return [
            'ids' => $property->getValue(),
        ];
    }
}
