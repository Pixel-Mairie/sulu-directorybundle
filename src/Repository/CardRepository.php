<?php

namespace Pixel\DirectoryBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Pixel\DirectoryBundle\Entity\Card;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

class CardRepository extends EntityRepository implements DataProviderRepositoryInterface
{
    use DataProviderRepositoryTrait;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, new ClassMetadata(Card::class));
    }

    public function create(string $locale): Card
    {
        $card = new Card();
        $card->setDefaultLocale($locale);
        $card->setLocale($locale);
        return $card;
    }

    public function save(Card $card): void
    {
        $this->getEntityManager()->persist($card);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id, string $locale): ?Card
    {
        $card = $this->find($id);
        if (! $card) {
            return null;
        }
        $card->setLocale($locale);
        return $card;
    }

    /**
     * @return array<Card>
     */
    public function findAllForSitemap(int $page, int $limit): array
    {
        $offset = ($page * $limit) - $limit;
        $criteria = [
            'isActive' => true,
        ];
        return $this->findBy($criteria, [], $limit, $offset);
    }

    public function countForSitemap(): int
    {
        $query = $this->createQueryBuilder('c')
            ->select('count(c)');
        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array<Card>
     */
    public function findWithSameCategory(int $categoryId, int $id): array
    {
        $query = $this->createQueryBuilder('card')
            ->leftJoin('card.category', 'category')
            ->where('category.id = :categoryId')
            ->andWhere('card.id != :id')
            ->setMaxResults(3)
            ->setParameter('categoryId', $categoryId)
            ->setParameter('id', $id);
        return $query->getQuery()->getResult();
    }

    /**
     * @param array<mixed> $categories
     * @return array<mixed>
     */
    public function findByCategories(array $categories, string $locale): array
    {
        $query = $this->createQueryBuilder('card')
            ->select('t.name, card.location, card.url, card.phoneNumber, card.email, card.isActive, t.routePath, c.id as category')
            ->leftJoin('card.category', 'c')
            ->leftJoin('card.translations', 't')
            ->where('c.id in (:categories)')
            ->andWhere('t.locale = :locale')
            ->andWhere('card.isActive = 1')
            ->setParameter('categories', $categories)
            ->setParameter('locale', $locale);
        return $query->getQuery()->getResult();
    }

    /**
     * @param string $alias
     * @param string $locale
     */
    public function appendJoins(QueryBuilder $queryBuilder, $alias, $locale): void
    {
        $queryBuilder->addSelect('category')->leftJoin($alias . '.category', 'category');
        //$queryBuilder->addSelect($alias.'.category');
    }

    /**
     * @param string $alias
     * @return string
     */
    public function appendCategoriesRelation(QueryBuilder $queryBuilder, $alias)
    {
        return $alias . '.category';
        //$queryBuilder->addSelect($alias.'.category');
    }

    protected function appendSortByJoins(QueryBuilder $queryBuilder, string $alias, string $locale): void
    {
        $queryBuilder->innerJoin($alias . '.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);
    }
}
