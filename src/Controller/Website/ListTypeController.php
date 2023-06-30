<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Controller\Website;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\DirectoryBundle\Entity\Card;
use Sulu\Bundle\WebsiteBundle\Controller\DefaultController;
use Sulu\Component\Content\Compat\StructureInterface;

class ListTypeController extends DefaultController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function getAttributes($attributes, StructureInterface $structure = null, $preview = false)
    {
        $attributes = parent::getAttributes($attributes, $structure, $preview);
        $cards = (isset($attributes['content']['types']['id'])) ? $this->entityManager->getRepository(Card::class)->findBy([
            'type' => $attributes['content']['types']['id'],
        ]) : false;
        $attributes['cards'] = false;
        if ($cards) {
            $sortCards = [];
            foreach ($cards as $card) {
                $sortCards[$card->getName()] = $card;
            }
            ksort($sortCards);
            $cards = [];
            foreach ($sortCards as $card) {
                $cards[] = $card;
            }
            $attributes['cards'] = $cards;
        }

        return $attributes;
    }
}
