<?php

namespace App\Repository;


use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\modeles\Filter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use function Sodium\add;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function filtreListeSorties(Filter $filter, Participant $userConnecte, Etat $sortiesPassees)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder
        -> where("s.etat != 7")
        ->orderBy('s.dateHeureDebut', 'DESC');

        if ($filter -> getCampus() !== null){
            $queryBuilder
                -> where("s.campus = :campus")
                -> setParameter("campus", $filter -> getCampus());

        }
        if($filter->getKeyWord() !== null){
            $queryBuilder
                ->andWhere("s.nom LIKE :keyWord")
                -> setParameter("keyWord", '%'.$filter->getKeyWord().'%');
        }

        if($filter->getDateDebut() !== null){
            $queryBuilder
                ->andWhere("s.dateHeureDebut > :dateDebut")
                -> setParameter("dateDebut", $filter->getDateDebut());
        }

        if($filter->getDateFin() !== null){
            $queryBuilder
                ->andWhere("s.dateHeureDebut < :dateFin")
                -> setParameter("dateFin", $filter->getDateFin());
        }

        if($filter->getOrganisateurSorties()){
            $queryBuilder
                ->andWhere("s.organisateur = :organisateurSorties")
                -> setParameter("organisateurSorties", $userConnecte);
        }

        if($filter->getInscritSorties() ){
            $queryBuilder
                ->andWhere(":inscritsSortie MEMBER OF s.participants ")
                -> setParameter("inscritsSortie", $userConnecte);
        }


        if($filter->getNonInscritSorties()){
            $queryBuilder
                -> andWhere(":inscritsSortie NOT MEMBER OF s.participants")
                -> setParameter("inscritsSortie", $userConnecte);
        }

        if($filter->getSortiesPassees()){
            $queryBuilder
                -> andWhere("s.etat = :sortiesPassees")
               -> setParameter("sortiesPassees", $sortiesPassees);
        }

        return $queryBuilder -> getQuery()->getResult();
    }


}
