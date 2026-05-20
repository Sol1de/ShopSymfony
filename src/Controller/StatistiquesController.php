<?php

namespace App\Controller;

use App\Service\StatistiqueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/statistiques')]
#[IsGranted('ROLE_ADMIN')]
final class StatistiquesController extends AbstractController
{
    public function __construct(
        private readonly StatistiqueService $statistiqueService,
    ) {}

    #[Route('', name: 'app_statistiques_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $annee = (int) ($request->query->get('annee') ?? date('Y'));

        return $this->render('statistiques/index.html.twig', [
            'annee'          => $annee,
            'caParMois'      => $this->statistiqueService->caParMois($annee),
            'top5Produits'   => $this->statistiqueService->top5Produits(),
            'tauxConversion' => $this->statistiqueService->tauxConversion(),
            'panierMoyen'    => $this->statistiqueService->panierMoyen(),
        ]);
    }
}
