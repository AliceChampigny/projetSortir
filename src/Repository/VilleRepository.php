<?php

namespace App\Repository;

use App\Entity\Ville;
use App\modeles\FilterVille;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ville>
 *
 * @method Ville|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ville|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ville[]    findAll()
 * @method Ville[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ville::class);
    }

    public function save(Ville $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ville $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function filterListeVille(FilterVille $filterVille)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        if($filterVille->getKeyWord() !== null){
            $queryBuilder
                ->andWhere("v.nom LIKE :keyWord")
                -> setParameter("keyWord", '%'.$filterVille->getKeyWord().'%');
        }

        return $queryBuilder -> getQuery()->getResult();
    }
}
