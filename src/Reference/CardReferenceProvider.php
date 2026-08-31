<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Reference;

use Pixel\DirectoryBundle\Entity\Card;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\ReferenceBundle\Application\Collector\ReferenceCollector;
use Sulu\Bundle\ReferenceBundle\Domain\Repository\ReferenceRepositoryInterface;

class CardReferenceProvider
{
    public function __construct(
        private ReferenceRepositoryInterface $referenceRepository,
    ) {
    }

    public function updateReferences(Card $card, string $locale, string $context): void
    {
        $referenceCollector = new ReferenceCollector(
            $this->referenceRepository,
            Card::RESOURCE_KEY,
            (string) $card->getId(),
            $locale,
            mb_substr($card->getName() ?? '', 0, 191),
            $context,
            ['id' => $card->getId(), 'locale' => $locale],
        );

        if ($logo = $card->getLogo()) {
            $referenceCollector->addReference(
                MediaInterface::RESOURCE_KEY,
                (string) $logo->getId(),
                'logo',
            );
        }

        foreach ($card->getMedias()['ids'] ?? [] as $id) {
            $referenceCollector->addReference(
                MediaInterface::RESOURCE_KEY,
                (string) $id,
                'medias',
            );
        }

        foreach ($card->getPdfs()['ids'] ?? [] as $id) {
            $referenceCollector->addReference(
                MediaInterface::RESOURCE_KEY,
                (string) $id,
                'pdfs',
            );
        }

        $referenceCollector->persistReferences();
        $this->referenceRepository->flush();
    }
}
