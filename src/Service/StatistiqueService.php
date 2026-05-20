<?php

namespace App\Service;

use App\Repository\CommandeRepository;
use App\Repository\LigneCommandeRepository;

class StatistiqueService
{
    public function __construct(
        private readonly CommandeRepository $commandeRepository,
        private readonly LigneCommandeRepository $ligneCommandeRepository,
    ) {}

    /**
     * Retourne le chiffre d'affaires par mois pour une année donnée.
     * Clés 1 à 12 (janvier à décembre), valeurs en euros.
     */
    public function caParMois(int $annee): array
    {
        $commandes = $this->commandeRepository->findValideesByAnnee($annee);
        $resultat  = array_fill(1, 12, 0.0);

        foreach ($commandes as $commande) {
            $mois = (int) $commande->getCreatedAt()->format('n');
            $resultat[$mois] += (float) $commande->getTotal();
        }

        return array_map(static fn (float $ca) => round($ca, 2), $resultat);
    }

    /**
     * Retourne les 5 produits les plus vendus (commandes validées).
     */
    public function top5Produits(): array
    {
        return $this->ligneCommandeRepository->findTop5Produits();
    }

    /**
     * Ratio commandes validées / commandes soumises (hors panier).
     * Ex : 0.73 signifie 73% de conversion.
     */
    public function tauxConversion(): float
    {
        return $this->commandeRepository->findTauxConversion();
    }

    /**
     * Montant moyen des commandes validées.
     */
    public function panierMoyen(): float
    {
        return $this->commandeRepository->findPanierMoyen();
    }
}
