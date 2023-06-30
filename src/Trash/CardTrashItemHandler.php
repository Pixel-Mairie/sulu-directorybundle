<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\DirectoryBundle\Admin\CardAdmin;
use Pixel\DirectoryBundle\Domain\Event\CardRestoredEvent;
use Pixel\DirectoryBundle\Entity\Card;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;

class CardTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    private TrashItemRepositoryInterface $trashItemRepository;

    private EntityManagerInterface $entityManager;

    private DoctrineRestoreHelperInterface $doctrineRestoreHelper;

    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        TrashItemRepositoryInterface $trashItemRepository,
        EntityManagerInterface $entityManager,
        DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        DomainEventCollectorInterface $domainEventCollector
    ) {
        $this->trashItemRepository = $trashItemRepository;
        $this->entityManager = $entityManager;
        $this->doctrineRestoreHelper = $doctrineRestoreHelper;
        $this->domainEventCollector = $domainEventCollector;
    }

    public static function getResourceKey(): string
    {
        return Card::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        $type = $resource->getType();
        $logo = $resource->getLogo();
        $category = $resource->getCategory();

        $data = [
            "name" => $resource->getName(),
            "description" => $resource->getDescription(),
            "slug" => $resource->getRoutePath(),
            "seo" => $resource->getSeo(),
            "typeId" => $type->getId(),
            "isActive" => $resource->getIsActive(),
            "location" => $resource->getLocation(),
            "logoId" => $logo ? $logo->getId() : null,
            "categoryId" => $category->getId(),
            "url" => $resource->getUrl(),
            "email" => $resource->getEmail(),
            "phoneNumber" => $resource->getPhoneNumber(),
            "facebook" => $resource->getFacebook(),
            "instagram" => $resource->getInstagram(),
            "twitter" => $resource->getTwitter(),
            "linkedin" => $resource->getLinkedin(),
        ];

        return $this->trashItemRepository->create(
            Card::RESOURCE_KEY,
            (string) $resource->getId(),
            $resource->getName(),
            $data,
            null,
            $options,
            Card::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $data = $trashItem->getRestoreData();
        $cardId = (int) $trashItem->getResourceId();
        $card = new Card();
        $card->setName($data['name']);
        $card->setDescription($data['description']);
        $card->setRoutePath($data['slug']);
        $card->setSeo($data['seo']);
        $card->setType($this->entityManager->find(CategoryInterface::class, $data['typeId']));
        $card->setIsActive($data['isActive']);
        $card->setLocation($data['location']);
        if ($data['logoId']) {
            $card->setLogo($this->entityManager->find(MediaInterface::class, $data['logoId']));
        }
        $card->setCategory($this->entityManager->find(CategoryInterface::class, $data['categoryId']));
        $card->setUrl($data['url']);
        $card->setEmail($data['email']);
        $card->setPhoneNumber($data['phoneNumber']);
        $card->setFacebook($data['facebook']);
        $card->setInstagram($data['instagram']);
        $card->setTwitter($data['twitter']);
        $card->setLinkedin($data['linkedin']);
        $this->domainEventCollector->collect(
            new CardRestoredEvent($card, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($card, $cardId);
        $this->createRoute($this->entityManager, $cardId, $card->getRoutePath(), Card::class);
        $this->entityManager->flush();
        return $card;
    }

    private function createRoute(EntityManagerInterface $manager, int $id, string $slug, string $class): void
    {
        $route = new Route();
        $route->setPath($slug);
        $route->setLocale('fr');
        $route->setEntityClass($class);
        $route->setEntityId((string) $id);
        $route->setHistory(false);
        $route->setCreated(new \DateTime());
        $route->setChanged(new \DateTime());
        $manager->persist($route);
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(null, CardAdmin::EDIT_FORM_VIEW, [
            'id' => 'id',
        ]);
    }
}
