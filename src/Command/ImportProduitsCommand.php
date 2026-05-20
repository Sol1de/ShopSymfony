<?php

namespace App\Command;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-produits',
    description: 'Importe des produits depuis un fichier CSV',
)]
class ImportProduitsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProduitRepository $produitRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('fichier', InputArgument::REQUIRED, 'Chemin vers le fichier CSV')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Format de sortie (table|json)', 'table')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io      = new SymfonyStyle($input, $output);
        $fichier = $input->getArgument('fichier');
        $format  = $input->getOption('format');

        if (!file_exists($fichier)) {
            $io->error("Le fichier \"$fichier\" n'existe pas.");
            return Command::FAILURE;
        }

        if (!in_array($format, ['table', 'json'])) {
            $io->error('Format invalide. Utilisez "table" ou "json".');
            return Command::INVALID;
        }

        $io->title("Import de produits depuis $fichier");

        $crees      = 0;
        $misAJour   = 0;
        $erreurs    = [];
        $row        = 0;

        $handle = fopen($fichier, 'r');
        if ($handle === false) {
            $io->error("Impossible d'ouvrir le fichier.");
            return Command::FAILURE;
        }

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row++;

            if ($row === 1) {
                continue;
            }

            $data = explode(';', $data[0]);

            try {
                if (count($data) < 4) {
                    throw new \RuntimeException('Ligne incomplète (attendu : reference;nom;description;prix;stock;actif)');
                }

                [$reference, $nom, $description, $prix] = $data;
                $stock = isset($data[4]) ? (int) $data[4] : 0;
                $actif = isset($data[5]) ? (bool)(int) $data[5] : true;

                if (empty($reference) || empty($nom)) {
                    throw new \RuntimeException('La référence et le nom sont obligatoires.');
                }

                if (!is_numeric($prix)) {
                    throw new \RuntimeException("Prix invalide : \"$prix\"");
                }

                $produit = $this->produitRepository->findOneBy(['reference' => $reference]);

                if ($produit === null) {
                    $produit = new Produit();
                    $produit->setReference($reference);
                    $crees++;
                } else {
                    $misAJour++;
                }

                $produit->setNom($nom);
                $produit->setDescription($description ?: null);
                $produit->setPrix($prix);
                $produit->setStock($stock);
                $produit->setActif($actif);

                $this->entityManager->persist($produit);

            } catch (\Throwable $e) {
                $erreurs[] = "Ligne $row : " . $e->getMessage();
            }
        }

        fclose($handle);
        $this->entityManager->flush();

        if ($format === 'json') {
            $output->writeln(json_encode([
                'crees'           => $crees,
                'mis_a_jour'      => $misAJour,
                'erreurs'         => count($erreurs),
                'detail_erreurs'  => $erreurs,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return empty($erreurs) ? Command::SUCCESS : Command::FAILURE;
        }

        $io->table(
            ['Statut', 'Nombre'],
            [
                ['Créés',      $crees],
                ['Mis à jour', $misAJour],
                ['Erreurs',    count($erreurs)],
            ]
        );

        if (!empty($erreurs)) {
            $io->warning('Détail des erreurs :');
            $io->listing($erreurs);
        }

        if ($crees === 0 && $misAJour === 0 && empty($erreurs)) {
            $io->warning('Aucun produit importé (fichier vide ?).');
            return Command::SUCCESS;
        }

        $io->success(sprintf('%d créé(s), %d mis à jour, %d erreur(s).', $crees, $misAJour, count($erreurs)));

        return empty($erreurs) ? Command::SUCCESS : Command::FAILURE;
    }
}
