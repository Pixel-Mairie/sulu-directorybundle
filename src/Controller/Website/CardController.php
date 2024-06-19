<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Controller\Website;

use Pixel\DirectoryBundle\Entity\Card;
use Pixel\DirectoryBundle\Repository\CardRepository;
use Sulu\Bundle\PreviewBundle\Preview\Preview;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CardController extends AbstractController
{
    private CardRepository $cardRepository;

    private TemplateAttributeResolverInterface $templateAttributeResolver;

    private RouteRepositoryInterface $routeRepository;

    private WebspaceManagerInterface $webspaceManager;

    public function __construct(CardRepository $cardRepository, TemplateAttributeResolverInterface $templateAttributeResolver, RouteRepositoryInterface $routeRepository, WebspaceManagerInterface $webspaceManager)
    {
        $this->cardRepository = $cardRepository;
        $this->templateAttributeResolver = $templateAttributeResolver;
        $this->routeRepository = $routeRepository;
        $this->webspaceManager = $webspaceManager;
    }

    /**
     * @param array<mixed> $attributes
     * @throws \Exception
     */
    public function indexAction(Card $card, array $attributes = [], bool $preview = false, bool $partial = false): Response
    {
        $relation = $this->getParameter('pixel_directory.relation');

        if (! $card->getSeo() || (isset($card->getSeo()['title']) && ! $card->getSeo()['title'])) {
            $seo = [
                "title" => $card->getName(),
            ];

            $card->setSeo($seo);
        }
        $parameters = $this->templateAttributeResolver->resolve([
            'card' => $card,
            'localizations' => $this->getLocalizationsArrayForEntity($card),
            'sameCategoryCards' => ($relation) ? $this->cardRepository->findWithSameCategory($card->getCategory()->getId(), $card->getId()) : false,
        ]);

        if ($partial) {
            return $this->renderBlock(
                '@Directory/card.html.twig',
                'content',
                $parameters
            );
        } elseif ($preview) {
            $content = $this->renderPreview(
                '@Directory/card.html.twig',
                $parameters
            );
        } else {
            if (! $card->getIsActive()) {
                throw $this->createNotFoundException();
            }
            $content = $this->renderView(
                '@Directory/card.html.twig',
                $parameters
            );
        }

        return new Response($content);
    }

    /**
     * @return array<string, array<string>>
     */
    protected function getLocalizationsArrayForEntity(Card $entity): array
    {
        $routes = $this->routeRepository->findAllByEntity(Card::class, (string) $entity->getId());

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->webspaceManager->findUrlByResourceLocator(
                $route->getPath(),
                null,
                $route->getLocale()
            );

            $localizations[$route->getLocale()] = [
                'locale' => $route->getLocale(),
                'url' => $url,
            ];
        }

        return $localizations;
    }

    /**
     * @param array<string> $parameters
     */
    protected function renderPreview(string $view, array $parameters = []): string
    {
        $parameters['previewParentTemplate'] = $view;
        $parameters['previewContentReplacer'] = Preview::CONTENT_REPLACER;
        //$album = $parameters['album'];

        return $this->renderView('@SuluWebsite/Preview/preview.html.twig', $parameters);
    }
}
