<?php

namespace App\Service;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;

class CommandeService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function valider(Commande $commande): void
    {
        $this->verifierStocks($commande);

        $total = $this->calculerTotal($commande);
        $commande->setTotal((string) $total);
        $commande->setStatut(Commande::STATUT_VALIDEE);
        $commande->setValidatedAt(new \DateTimeImmutable());

        $this->decrementerStocks($commande);
        $this->em->flush();
    }

    public function calculerTotal(Commande $commande): float
    {
        $total = 0.0;
        foreach ($commande->getLignes() as $ligne) {
            $total += (float) $ligne->getPrixUnitaire() * $ligne->getQuantite();
        }

        // Remise de 10% pour les clients VIP
        if ($commande->getClient()?->isVip()) {
            $total *= 0.90;
        }

        return round($total, 2);
    }

    private function verifierStocks(Commande $commande): void
    {
        foreach ($commande->getLignes() as $ligne) {
            $produit = $ligne->getProduit();
            if ($produit->getStock() < $ligne->getQuantite()) {
                throw new \RuntimeException(sprintf(
                    'Stock insuffisant pour "%s" (disponible : %d, demandé : %d)',
                    $produit->getNom(),
                    $produit->getStock(),
                    $ligne->getQuantite()
                ));
            }
        }
    }

    private function decrementerStocks(Commande $commande): void
    {
        foreach ($commande->getLignes() as $ligne) {
            $produit = $ligne->getProduit();
            $produit->setStock($produit->getStock() - $ligne->getQuantite());
        }
    }
}
