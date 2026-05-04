<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Reference;

use Pixel\DirectoryBundle\Entity\Card;
use Pixel\DirectoryBundle\Repository\CardRepository;
use Sulu\Bundle\ReferenceBundle\Application\Refresh\ReferenceRefresherInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class CardReferenceRefresher implements ReferenceRefresherInterface
{
    public function __construct(
        private CardReferenceProvider $cardReferenceProvider,
        private CardRepository $cardRepository,
        private WebspaceManagerInterface $webspaceManager,
        private string $suluContext,
    ) {
    }

    public static function getResourceKey(): string
    {
        return Card::RESOURCE_KEY;
    }

    public function refresh(): \Generator
    {
        $locales = $this->webspaceManager->getAllLocales();
        $cards = $this->cardRepository->findAll();

        foreach ($cards as $card) {
            foreach ($locales as $locale) {
                $card->setLocale($locale);
                $this->cardReferenceProvider->updateReferences($card, $locale, $this->suluContext);
            }

            yield $card;
        }
    }
}
