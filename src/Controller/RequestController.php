<?php

namespace App\Controller;

use App\Entity\SapRequest;
use App\Form\SapRequestType;
use App\Repository\AtividadeRepository;
use App\Repository\ProjetoRepository;
use App\Repository\SapRequestRepository;
use App\Service\RequestXlsxExporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/requests')]
class RequestController extends AbstractController
{
    #[Route('', name: 'app_request_index', methods: ['GET'])]
    public function index(
        Request $request,
        SapRequestRepository $repository,
        ProjetoRepository $projetoRepository,
        AtividadeRepository $atividadeRepository
    ): Response {
        $filtros = $this->obterFiltros($request);

        return $this->render('request/index.html.twig', [
            'requests' => $repository->buscarComFiltros($filtros),
            'filtros' => $filtros,
            'projetos' => $projetoRepository->findBy([], ['nome' => 'ASC']),
            'atividades' => $atividadeRepository->findComRequestsParaFiltro(),
            'tiposRequest' => $this->tiposRequest(),
            'statusRequest' => $this->statusRequest(),
        ]);
    }

    #[Route('/exportar', name: 'app_request_exportar', methods: ['GET'])]
    public function exportar(
        Request $request,
        SapRequestRepository $repository,
        RequestXlsxExporter $exporter
    ): Response {
        $filtros = $this->obterFiltros($request);
        $requests = $repository->buscarComFiltros($filtros);

        if ($requests === []) {
            $this->addFlash('error', 'Nao ha requests para exportar com os filtros selecionados.');

            return $this->redirectToRoute('app_request_index', $filtros);
        }

        try {
            $conteudo = $exporter->gerarArquivo($requests);
        } catch (\RuntimeException) {
            $this->addFlash('error', 'Nao foi possivel gerar o arquivo XLSX no momento.');

            return $this->redirectToRoute('app_request_index', $filtros);
        }

        $nomeArquivo = sprintf('requests-%s.xlsx', (new \DateTimeImmutable())->format('Ymd-His'));

        return new Response($conteudo, Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $nomeArquivo),
            'Cache-Control' => 'max-age=0, must-revalidate',
        ]);
    }

    #[Route('/{id}/editar', name: 'app_request_edit', methods: ['GET', 'POST'])]
    public function edit(SapRequest $sapRequest, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SapRequestType::class, $sapRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Request atualizada com sucesso.');

            $atividadeId = $sapRequest->getAtividade()?->getId();
            if ($atividadeId) {
                return $this->redirectToRoute('app_atividade_show', ['id' => $atividadeId]);
            }

            return $this->redirectToRoute('app_request_index');
        }

        return $this->render('request/edit.html.twig', [
            'sapRequest' => $sapRequest,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/excluir', name: 'app_request_delete', methods: ['POST'])]
    public function delete(SapRequest $sapRequest, Request $request, EntityManagerInterface $em): Response
    {
        $atividadeId = $sapRequest->getAtividade()?->getId();

        if ($this->isCsrfTokenValid('delete_request_'.$sapRequest->getId(), (string) $request->request->get('_token'))) {
            $em->remove($sapRequest);
            $em->flush();
            $this->addFlash('success', 'Request excluída com sucesso.');
        } else {
            $this->addFlash('error', 'Falha de segurança ao excluir request (CSRF inválido).');
        }

        if ($atividadeId) {
            return $this->redirectToRoute('app_atividade_show', ['id' => $atividadeId]);
        }

        return $this->redirectToRoute('app_request_index');
    }

    /**
     * @return array<string, mixed>
     */
    private function obterFiltros(Request $request): array
    {
        return [
            'projeto' => $request->query->get('projeto'),
            'atividade' => $request->query->get('atividade'),
            'tipo' => $request->query->get('tipo'),
            'modulo' => $request->query->get('modulo'),
            'status' => $request->query->get('status'),
            'numero' => $request->query->get('numero'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function tiposRequest(): array
    {
        return [
            'Workbench',
            'Customizing',
            'Transport of Copies',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function statusRequest(): array
    {
        return [
            'Modificável',
            'Liberada',
            'Importada QA',
            'Importada PRD',
        ];
    }
}
