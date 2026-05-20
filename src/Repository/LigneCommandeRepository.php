<?php

namespace App\Repository;

use App\Entity\LigneCommande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LigneCommande>
 */
class LigneCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneCommande::class);
    }

    public function findTop5Produits(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p.nom, p.reference, SUM(l.quantite) AS total_vendu
                 FROM App\Entity\LigneCommande l
                 JOIN l.produit p
                 JOIN l.commande c
                 WHERE c.statut = :statut
                 GROUP BY p.id, p.nom, p.reference
                 ORDER BY total_vendu DESC'
            )
            ->setMaxResults(5)
            ->setParameter('statut', \App\Entity\Commande::STATUT_VALIDEE)
            ->getResult();
    }
}
