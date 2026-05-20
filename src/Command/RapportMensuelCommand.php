<?php

namespace App\Command;

use App\Service\StatistiqueService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:rapport-mensuel',
    description: 'Génère le rapport mensuel des ventes',
)]
class RapportMensuelCommand extends Command
{
    public function __construct(
        private readonly StatistiqueService $statistiqueService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('mois', InputArgument::REQUIRED, 'Mois au format YYYY-MM')
            ->addArgument('annee', InputArgument::OPTIONAL, 'Année', date('Y'))
            ->addArgument('ids', InputArgument::IS_ARRAY, 'Liste d\'IDs')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Format de sortie (csv|json)', 'csv')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulation sans écriture')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Fichier de sortie')
            ->addOption('email', null, InputOption::VALUE_NONE, 'Envoyer le rapport par email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $mois   = $input->getArgument('mois');
        $format = $input->getOption('format');
        $email  = $input->getOption('email');

        $io->title("Génération du rapport - $mois");

        if (!preg_match('/^\d{4}-\d{2}$/', $mois)) {
            $io->error('Format de mois invalide. Utilisez YYYY-MM (ex: 2024-03)');
            return Command::FAILURE;
        }

        $annee = (int) explode('-', $mois)[0];

        $io->section('Statistiques générales');
        $io->table(
            ['Indicateur', 'Valeur'],
            [
                ['Taux de conversion', number_format($this->statistiqueService->tauxConversion() * 100, 1) . ' %'],
                ['Panier moyen', number_format($this->statistiqueService->panierMoyen(), 2) . ' €'],
            ]
        );

        $moisNoms  = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $caParMois = $this->statistiqueService->caParMois($annee);

        $io->section("Chiffre d'affaires par mois ($annee)");
        $rows = [];
        foreach ($caParMois as $num => $ca) {
            $rows[] = [$moisNoms[$num], number_format($ca, 2) . ' €'];
        }
        $io->table(['Mois', 'CA'], $rows);

        $io->section('Top 5 produits');
        $top5 = $this->statistiqueService->top5Produits();
        if (empty($top5)) {
            $io->info('Aucune vente enregistrée.');
        } else {
            $io->table(
                ['Produit', 'Référence', 'Qté vendue'],
                array_map(static fn ($p) => [$p['nom'], $p['reference'], $p['total_vendu']], $top5)
            );
        }

        $io->info("Format de sortie : $format");

        if ($email) {
            $io->note('Envoi par email activé');
        }

        $io->success("Rapport $mois généré avec succès !");

        return Command::SUCCESS;
    }
}
