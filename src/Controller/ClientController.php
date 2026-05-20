<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Service\FideliteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client')]
#[IsGranted('ROLE_USER')]
final class ClientController extends AbstractController
{
    public function __construct(
        private readonly FideliteService $fideliteService,
    ) {}

    #[Route('/fidelite', name: 'app_client_fidelite', methods: ['GET'])]
    public function fidelite(): Response
    {
        /** @var Client $client */
        $client = $this->getUser();
        $score  = $this->fideliteService->calculerScore($client);
        $niveau = $this->fideliteService->getNiveau($score);

        return $this->render('client/fidelite.html.twig', [
            'client' => $client,
            'score'  => $score,
            'niveau' => $niveau,
        ]);
    }

    #[Route('', name: 'app_client_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(ClientRepository $clientRepository): Response
    {
        $clients = $clientRepository->findAll();
        $data    = [];

        foreach ($clients as $client) {
            $score  = $this->fideliteService->calculerScore($client);
            $data[] = [
                'client' => $client,
                'score'  => $score,
                'niveau' => $this->fideliteService->getNiveau($score),
            ];
        }

        return $this->render('client/index.html.twig', [
            'clients' => $data,
        ]);
    }
}
