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

    public function __construct(CardRepository $cardRepository)
    {
        $this->cardRepository = $cardRepository;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedServices()
    {
        $subscribedServices = parent::getSubscribedServices();

        $subscribedServices['sulu_core.webspace.webspace_manager'] = WebspaceManagerInterface::class;
        $subscribedServices['sulu.repository.route'] = RouteRepositoryInterface::class;
        $subscribedServices['sulu_website.resolver.template_attribute'] = TemplateAttributeResolverInterface::class;

        return $subscribedServices;
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
        $parameters = $this->get('sulu_website.resolver.template_attribute')->resolve([
            'card' => $card,
            'localizations' => $this->getLocalizationsArrayForEntity($card),
            'sameCategoryCards' => ($relation) ? $this->cardRepository->findWithSameCategory($card->getCategory()->getId(), $card->getId()) : false,
        ]);

        if ($partial) {
            $content = $this->renderBlock(
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
        $routes = $this->get('sulu.repository.route')->findAllByEntity(Card::class, (string) $entity->getId());

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->get('sulu_core.webspace.webspace_manager')->findUrlByResourceLocator(
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
     * Returns rendered part of template specified by block.
     *
     * @param mixed $template
     * @param mixed $block
     * @param mixed $attributes
     */
    protected function renderBlock($template, $block, $attributes = []): string
    {
        $twig = $this->get('twig');
        $attributes = $twig->mergeGlobals($attributes);

        $template = $twig->load($template);

        $level = ob_get_level();
        ob_start();

        try {
            $rendered = $template->renderBlock($block, $attributes);
            ob_end_clean();

            return $rendered;
        } catch (\Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }
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
