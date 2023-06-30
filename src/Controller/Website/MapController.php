<?php

declare(strict_types=1);

namespace Pixel\DirectoryBundle\Controller\Website;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\DirectoryBundle\Entity\Card;
use Pixel\DirectoryBundle\Repository\CardRepository;
use Sulu\Bundle\WebsiteBundle\Controller\DefaultController;
use Sulu\Component\Content\Compat\StructureInterface;

class MapController extends DefaultController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    protected CardRepository $cardRepository;

    public function __construct(EntityManagerInterface $entityManager, CardRepository $cardRepository)
    {
        $this->entityManager = $entityManager;
        $this->cardRepository = $cardRepository;
    }

    protected function getAttributes($attributes, StructureInterface $structure = null, $preview = false)
    {
        $attributes = parent::getAttributes($attributes, $structure, $preview);

        $filterRequest = ($this->getRequest()->get('filter')) ? $this->getRequest()->get('filter') : false;
        $attributes['filters'] = false;
        $attributes['isFilterView'] = false;
        if (isset($attributes['content']['filters'])) {
            $attributes['filters'] = $attributes['content']['filters'];
            $categories = [];
            $colors = [];
            $filters = [];
            foreach ($attributes['filters'] as $filter) {
                foreach ($filter['categories'] as $category) {
                    $categories[] = $category['id'];
                    $colors[$category['id']] = $filter['color'];
                    $filters[$filter['filter']][] = $category['id'];
                }
            }

            if ($filterRequest) {
                $categories = $filters[$filterRequest];
                $attributes['isFilterView'] = true;
                $attributes['filterName'] = $filterRequest;
            }

            $cards = $this->cardRepository->findByCategories($categories, $attributes['request']['defaultLocale']);
            foreach ($cards as $k => $v) {
                $cards[$k]['color'] = $colors[$v['category']];
            }

            $attributes['cards'] = $cards;
        }

        return $attributes;
    }
}
