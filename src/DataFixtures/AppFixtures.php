<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Créer des produits
        $produitsData = [
            ['REF001', 'Ordinateur portable', 1299.99, 25],
            ['REF002', 'Souris sans fil', 29.99, 150],
            ['REF003', 'Clavier mécanique', 89.90, 80],
            ['REF004', 'Écran 27 pouces', 349.00, 40],
            ['REF005', 'Casque audio', 79.99, 60],
            ['REF006', 'Webcam HD', 59.90, 100],
            ['REF007', 'Micro USB', 39.99, 75],
            ['REF008', 'Hub USB-C', 24.99, 200],
            ['REF009', 'Tapis de souris', 12.99, 300],
            ['REF010', 'Support laptop', 34.99, 50],
        ];

        $produits = [];
        foreach ($produitsData as [$ref, $nom, $prix, $stock]) {
            $produit = new Produit();
            $produit
                ->setReference($ref)
                ->setNom($nom)
                ->setDescription("Description de {$nom}")
                ->setPrix((string) $prix)
                ->setStock($stock);

            $manager->persist($produit);
            $produits[] = $produit;
        }

        // Créer des clients
        $clients = [];
        $clientsData = [
            ['alice@example.com',   'Alice',   'Martin', false],
            ['bob@example.com',     'Bob',     'Dupont', true],
            ['charlie@example.com', 'Charlie', 'Durand', false],
        ];

        foreach ($clientsData as [$email, $prenom, $nom, $vip]) {
            $client = new Client();
            $client
                ->setEmail($email)
                ->setPrenom($prenom)
                ->setNom($nom)
                ->setVip($vip)
                ->setPassword($this->passwordHasher->hashPassword($client, 'password'));

            $manager->persist($client);
            $clients[] = $client;
        }

        // Créer des commandes et lignes commandes
        // [$client, $statut, [[$indexProduit, $quantite], ...]]
        $commandesData = [
            [$clients[0], Commande::STATUT_LIVREE,     [[0, 1], [1, 2]]],
            [$clients[0], Commande::STATUT_VALIDEE,    [[2, 1], [3, 1]]],
            [$clients[1], Commande::STATUT_EXPEDIEE,   [[0, 2], [4, 1], [7, 3]]],
            [$clients[1], Commande::STATUT_EN_ATTENTE, [[5, 1]]],
            [$clients[2], Commande::STATUT_PANIER,     [[8, 2], [9, 1]]],
        ];

        foreach ($commandesData as [$client, $statut, $lignes]) {
            $commande = new Commande();
            $commande
                ->setClient($client)
                ->setStatut($statut);

            if ($statut !== Commande::STATUT_PANIER) {
                $commande->setValidatedAt(new \DateTimeImmutable());
            }

            $total = 0.0;
            foreach ($lignes as [$produitIndex, $quantite]) {
                $produit = $produits[$produitIndex];

                $ligne = new LigneCommande();
                $ligne
                    ->setCommande($commande)
                    ->setProduit($produit)
                    ->setQuantite($quantite)
                    ->setPrixUnitaire($produit->getPrix());

                $manager->persist($ligne);
                $total += (float) $produit->getPrix() * $quantite;
            }

            $commande->setTotal((string) round($total, 2));
            $manager->persist($commande);
        }

        $manager->flush();
    }
}
