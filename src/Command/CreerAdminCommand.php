<?php

namespace App\Command;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:creer-admin',
    description: 'Crée un utilisateur administrateur de manière interactive',
)]
class CreerAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ClientRepository $clientRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création d\'un administrateur');

        $email = $io->ask('Email', null, function (?string $value): string {
            if (empty($value)) {
                throw new \RuntimeException('L\'email ne peut pas être vide.');
            }
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Format d\'email invalide.');
            }
            return $value;
        });

        if ($this->clientRepository->findOneBy(['email' => $email])) {
            $io->error("Un utilisateur avec l'email \"$email\" existe déjà.");
            return Command::FAILURE;
        }

        $password = $io->askHidden('Mot de passe', function (?string $value): string {
            if (empty($value)) {
                throw new \RuntimeException('Le mot de passe ne peut pas être vide.');
            }
            if (strlen($value) < 6) {
                throw new \RuntimeException('Le mot de passe doit contenir au moins 6 caractères.');
            }
            return $value;
        });

        $io->askHidden('Confirmez', function (?string $value) use ($password): string {
            if ($value !== $password) {
                throw new \RuntimeException('Les mots de passe ne correspondent pas.');
            }
            return $value;
        });

        $role = $io->choice('Rôle', ['ROLE_ADMIN', 'ROLE_MODERATEUR', 'ROLE_EDITOR'], 'ROLE_ADMIN');

        $prenom = $io->ask('Prénom', null, function (?string $value): string {
            if (empty($value)) {
                throw new \RuntimeException('Le prénom ne peut pas être vide.');
            }
            return $value;
        });

        $nom = $io->ask('Nom', null, function (?string $value): string {
            if (empty($value)) {
                throw new \RuntimeException('Le nom ne peut pas être vide.');
            }
            return $value;
        });

        $io->section('Récapitulatif');
        $io->table([], [
            ['Email',  $email],
            ['Prénom', $prenom],
            ['Nom',    $nom],
            ['Rôle',   $role],
        ]);

        if (!$io->confirm('Créer cet utilisateur ?', false)) {
            $io->warning('Création annulée.');
            return Command::SUCCESS;
        }

        $client = new Client();
        $client->setEmail($email);
        $client->setPrenom($prenom);
        $client->setNom($nom);
        $client->setRoles([$role]);
        $client->setPassword($this->passwordHasher->hashPassword($client, $password));

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $io->success('Utilisateur créé avec succès !');

        return Command::SUCCESS;
    }
}
