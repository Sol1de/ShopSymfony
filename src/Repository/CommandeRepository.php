<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /** @return Commande[] */
    public function findValideesByAnnee(int $annee): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.statut = :statut')
            ->andWhere('c.createdAt >= :debut')
            ->andWhere('c.createdAt < :fin')
            ->setParameter('statut', Commande::STATUT_VALIDEE)
            ->setParameter('debut', new \DateTimeImmutable("$annee-01-01"))
            ->setParameter('fin', new \DateTimeImmutable(($annee + 1) . '-01-01'))
            ->getQuery()
            ->getResult();
    }

    public function findTauxConversion(): float
    {
        $total = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.statut != :statut')
            ->setParameter('statut', Commande::STATUT_PANIER)
            ->getQuery()
            ->getSingleScalarResult();

        if ($total === 0) {
            return 0.0;
        }

        $validees = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.statut = :statut')
            ->setParameter('statut', Commande::STATUT_VALIDEE)
            ->getQuery()
            ->getSingleScalarResult();

        return round($validees / $total, 2);
    }

    public function findPanierMoyen(): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('AVG(c.total)')
            ->where('c.statut = :statut')
            ->setParameter('statut', Commande::STATUT_VALIDEE)
            ->getQuery()
            ->getSingleScalarResult();

        return round((float) $result, 2);
    }
}
