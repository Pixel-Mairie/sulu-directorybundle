<?php

namespace Pixel\DirectoryBundle\Routing;

use Pixel\DirectoryBundle\Controller\Website\CardController;
use Pixel\DirectoryBundle\Entity\Card;
use Pixel\DirectoryBundle\Repository\CardRepository;
use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;

class CardRouteDefaultsProvider implements RouteDefaultsProviderInterface
{
    private CardRepository $cardRepository;

    public function __construct(CardRepository $cardRepository)
    {
        $this->cardRepository = $cardRepository;
    }

    /**
     * @return mixed[]
     */
    public function getByEntity($entityClass, $id, $locale, $object = null)
    {
        return [
            '_controller' => CardController::class . '::indexAction',
            'card' => $object ?: $this->cardRepository->findById((int) $id, $locale),
        ];
    }

    public function isPublished($entityClass, $id, $locale)
    {
        return true;
    }

    public function supports($entityClass)
    {
        return Card::class === $entityClass;
    }
}
