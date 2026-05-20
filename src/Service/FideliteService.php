<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Commande;

class FideliteService
{
    public function calculerScore(Client $client): int
    {
        $score = 0;

        $totalAchats = 0.0;
        foreach ($client->getCommandes() as $commande) {
            if ($commande->getStatut() === Commande::STATUT_VALIDEE) {
                $score += 1;
                $totalAchats += (float) $commande->getTotal();
            }
        }

        // +20 bonus si le client est inscrit depuis 2 ans ou plus
        $deuxAns = new \DateTimeImmutable('-2 years');
        if ($client->getCreatedAt() <= $deuxAns) {
            $score += 20;
        }

        // +50 bonus si le total des achats dépasse 1 000 €
        if ($totalAchats > 1000.0) {
            $score += 50;
        }

        return $score;
    }

    public function getNiveau(int $score): string
    {
        return match (true) {
            $score >= 600 => 'Platine',
            $score >= 300 => 'Or',
            $score >= 100 => 'Argent',
            default       => 'Bronze',
        };
    }
}
