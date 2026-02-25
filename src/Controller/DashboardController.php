<?php

namespace App\Controller;

use App\Repository\ProjetoRepository;
use App\Repository\SapRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(ProjetoRepository $projetoRepository, SapRequestRepository $requestRepository): Response
    {
        $projetos = $projetoRepository->findBy([], ['id' => 'DESC']);
        $requestsRecentes = $requestRepository->findBy([], ['id' => 'DESC'], 8);

        return $this->render('dashboard/index.html.twig', [
            'projetos' => $projetos,
            'requestsRecentes' => $requestsRecentes,
        ]);
    }
}
