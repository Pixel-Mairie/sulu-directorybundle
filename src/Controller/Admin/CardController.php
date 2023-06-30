<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Pixel\DirectoryBundle\Common\DoctrineListRepresentationFactory;
use Pixel\DirectoryBundle\Domain\Event\CardCreatedEvent;
use Pixel\DirectoryBundle\Domain\Event\CardModifiedEvent;
use Pixel\DirectoryBundle\Domain\Event\CardRemovedEvent;
use Pixel\DirectoryBundle\Entity\Card;
use Pixel\DirectoryBundle\Repository\CardRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Security\SecuredControllerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

//use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
//use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;

/**
 * @RouteResource("card")
 */
class CardController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use RequestParametersTrait;

    private DoctrineListRepresentationFactory $doctrineListRepresentationFactory;

    private EntityManagerInterface $entityManager;

    private MediaManagerInterface $mediaManager;

    private CategoryManagerInterface $categoryManager;

    private WebspaceManagerInterface $webspaceManager;

    private RouteManagerInterface $routeManager;

    private RouteRepositoryInterface $routeRepository;

    private TrashManagerInterface $trashManager;

    private DomainEventCollectorInterface $domainEventCollector;

    private CardRepository $repository;

    public function __construct(
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        EntityManagerInterface $entityManager,
        MediaManagerInterface $mediaManager,
        ViewHandlerInterface $viewHandler,
        CategoryManagerInterface $categoryManager,
        WebspaceManagerInterface $webspaceManager,
        RouteManagerInterface $routeManager,
        RouteRepositoryInterface $routeRepository,
        TrashManagerInterface $trashManager,
        DomainEventCollectorInterface $domainEventCollector,
        CardRepository $repository,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->entityManager = $entityManager;
        $this->mediaManager = $mediaManager;
        $this->categoryManager = $categoryManager;
        $this->webspaceManager = $webspaceManager;
        $this->routeManager = $routeManager;
        $this->routeRepository = $routeRepository;
        $this->trashManager = $trashManager;
        $this->domainEventCollector = $domainEventCollector;
        $this->repository = $repository;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $locale = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Card::RESOURCE_KEY,
            [],
            [
                'locale' => $locale,
            ]
        );

        return $this->handleView($this->view($listRepresentation));
    }

    public function getAction(int $id, Request $request): Response
    {
        $item = $this->load($id, $request);
        if (! $item) {
            throw new NotFoundHttpException();
        }

        if ($item->getName() === null && $item->getDefaultLocale()) {
            $request->setMethod($item->getDefaultLocale());
            $item = $this->load($id, $request, $item->getDefaultLocale());
        }

        return $this->handleView($this->view($item));
    }

    protected function load(int $id, Request $request, string $defaultLocale = null): ?Card
    {
        return $this->repository->findById($id, ($defaultLocale) ? $defaultLocale : (string) $this->getLocale($request));
    }

    public function putAction(Request $request, int $id): Response
    {
        $item = $this->load($id, $request);
        if (! $item) {
            throw new NotFoundHttpException();
        }

        $data = $request->request->all();
        $this->mapDataToEntity($data, $item, $request);
        $this->updateRoutesForEntity($item);
        $this->domainEventCollector->collect(
            new CardModifiedEvent($item, $data)
        );
        $this->entityManager->flush();
        $this->save($item);

        return $this->handleView($this->view($item));
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function mapDataToEntity(array $data, Card $entity, Request $request): void
    {
        $logoId = $data['logo']['id'] ?? null;
        $location = $data['location'] ?? null;
        $seo = (isset($data['ext']['seo'])) ? $data['ext']['seo'] : null;
        $description = $data['description'] ?? null;
        $isActive = $data['isActive'] ?? null;
        $typeId = (isset($data['type']['id'])) ? $data['type']['id'] : $data['type'];
        $categoryId = (isset($data['category']['id'])) ? $data['category']['id'] : $data['category'];
        $url = $data['url'] ?? null;
        $email = $data['email'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $facebook = $data['facebook'] ?? null;
        $instagram = $data['instagram'] ?? null;
        $twitter = $data['twitter'] ?? null;
        $linkedin = $data['linkedin'] ?? null;
        $medias = $data['medias'] ?? null;
        $pdfs = $data['pdfs'] ?? null;
        $youtubeId = $data['youtubeId'] ?? null;

        $entity->setName($data['name']);
        $entity->setLocation($location);
        $entity->setRoutePath($data['routePath']);
        $entity->setType($this->categoryManager->findById($typeId));
        $entity->setIsActive($isActive);
        $entity->setDescription($description);
        $entity->setSeo($seo);
        $entity->setCategory($this->categoryManager->findById($categoryId));
        $entity->setLogo($logoId ? $this->mediaManager->getEntityById($logoId) : null);
        $entity->setUrl($url);
        $entity->setEmail($email);
        $entity->setPhoneNumber($phoneNumber);
        $entity->setFacebook($facebook);
        $entity->setInstagram($instagram);
        $entity->setTwitter($twitter);
        $entity->setLinkedin($linkedin);
        $entity->setMedias($medias);
        $entity->setPdfs($pdfs);
        $entity->setYoutubeId($youtubeId);
    }

    protected function updateRoutesForEntity(Card $entity): void
    {
        // create route for all locales of the application because event entity is not localized
        foreach ($this->webspaceManager->getAllLocales() as $locale) {
            $this->routeManager->createOrUpdateByAttributes(
                Card::class,
                (string) $entity->getId(),
                $locale,
                $entity->getRoutePath(),
            );
        }
    }

    protected function save(Card $card): void
    {
        $this->repository->save($card);
    }

    public function postAction(Request $request): Response
    {
        $item = $this->create($request);
        $data = $request->request->all();
        $this->mapDataToEntity($data, $item, $request);
        $this->save($item);
        $this->updateRoutesForEntity($item);
        $this->domainEventCollector->collect(
            new CardCreatedEvent($item, $data)
        );
        $this->entityManager->flush();
        return $this->handleView($this->view($item, 201));
    }

    protected function create(Request $request): Card
    {
        return $this->repository->create((string) $this->getLocale($request));
    }

    public function deleteAction(int $id): Response
    {
        /** @var Card $card */
        $card = $this->entityManager->getRepository(Card::class)->find($id);
        $cardName = $card->getName();
        if ($card) {
            $this->trashManager->store(Card::RESOURCE_KEY, $card);
            $this->entityManager->remove($card);
            $this->removeRoutesForEntity($card);
            $this->domainEventCollector->collect(
                new CardRemovedEvent($id, $cardName)
            );
        }
        $this->entityManager->flush();

        return $this->handleView($this->view(null, 204));
    }

    protected function removeRoutesForEntity(Card $entity): void
    {
        // remove route for all locales of the application because event entity is not localized
        foreach ($this->webspaceManager->getAllLocales() as $locale) {
            $routes = $this->routeRepository->findAllByEntity(
                Card::class,
                (string) $entity->getId(),
                $locale
            );

            foreach ($routes as $route) {
                $this->routeRepository->remove($route);
            }
        }
    }

    public function getSecurityContext(): string
    {
        return Card::SECURITY_CONTEXT;
    }

    /**
     * @Rest\Post("/cards/{id}")
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityNotFoundException
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);
        $locale = $this->getRequestParameter($request, 'locale', true);

        try {
            switch ($action) {
                case 'enable':
                    $item = $this->entityManager->getRepository(Card::class)->find($id);
                    $item->setLocale($locale);
                    $item->setIsActive(true);
                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                    break;
                case 'disable':
                    $item = $this->entityManager->getRepository(Card::class)->find($id);
                    $item->setLocale($locale);
                    $item->setIsActive(false);
                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                    break;
                default:
                    throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action));
            }
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
            return $this->handleView($view);
        }

        return $this->handleView($this->view($item));
    }
}
