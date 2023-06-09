<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Category::class);
	}

	public function save(Category $entity, bool $flush = false): void
	{
		$this->getEntityManager()->persist($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function remove(Category $entity, bool $flush = false): void
	{
		$this->getEntityManager()->remove($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	//    /**
	//     * @return Category[] Returns an array of Category objects
	//     */
	public function findLastThreeCategories(): array
	{
		return $this->createQueryBuilder('c')
			->select('c.id, c.title') // Choix des colonnes à récupérer
			->orderBy('c.id', 'DESC')
			->setMaxResults(3)
			->getQuery()
			->getResult();;
	}

	public function findOneById($value)
	{
		return $this->createQueryBuilder('c')
			->select('c.id,c.title')
			->andWhere('c.id = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult();
	}
}
