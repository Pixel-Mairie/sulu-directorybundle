<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Link;

use Pixel\DirectoryBundle\Entity\Card;
use Pixel\DirectoryBundle\Repository\CardRepository;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfigurationBuilder;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DirectoryLinkProvider implements LinkProviderInterface
{
    private CardRepository $cardRepository;

    private TranslatorInterface $translator;

    public function __construct(CardRepository $cardRepository, TranslatorInterface $translator)
    {
        $this->cardRepository = $cardRepository;
        $this->translator = $translator;
    }

    public function getConfiguration()
    {
        return LinkConfigurationBuilder::create()
            ->setTitle($this->translator->trans('directory'))
            ->setResourceKey(Card::RESOURCE_KEY) // the resourceKey of the entity that should be loaded
            ->setListAdapter('table')
            ->setDisplayProperties(['name'])
            ->setOverlayTitle($this->translator->trans('directory'))
            ->setEmptyText($this->translator->trans('card.emptyCard'))
            ->setIcon('fa-map')
            ->getLinkConfiguration();
    }

    public function preload(array $hrefs, $locale, $published = true): array
    {
        $result = [];
        if (0 === count($hrefs)) {
            return $result;
        }

        $items = $this->cardRepository->findBy([
            'id' => $hrefs,
        ]); // load items by id
        foreach ($items as $item) {
            $result[] = new LinkItem($item->getId(), $item->getName(), $item->getRoutePath(), $item->getIsActive()); // create link-item foreach item
        }

        return $result;
    }
}
