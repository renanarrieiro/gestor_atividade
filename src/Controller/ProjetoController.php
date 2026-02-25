<?php

namespace App\Controller;

use App\Entity\Projeto;
use App\Form\ProjetoType;
use App\Repository\ProjetoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projetos')]
class ProjetoController extends AbstractController
{
    #[Route('', name: 'app_projeto_index', methods: ['GET'])]
    public function index(ProjetoRepository $repository): Response
    {
        return $this->render('projeto/index.html.twig', [
            'projetos' => $repository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/novo', name: 'app_projeto_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $projeto = new Projeto();
        $form = $this->createForm(ProjetoType::class, $projeto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projeto);
            $em->flush();

            return $this->redirectToRoute('app_projeto_show', ['id' => $projeto->getId()]);
        }

        return $this->render('projeto/new.html.twig', [
            'projeto' => $projeto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/editar', name: 'app_projeto_edit', methods: ['GET', 'POST'])]
    public function edit(Projeto $projeto, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProjetoType::class, $projeto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Projeto atualizado com sucesso.');

            return $this->redirectToRoute('app_projeto_show', ['id' => $projeto->getId()]);
        }

        return $this->render('projeto/edit.html.twig', [
            'projeto' => $projeto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_projeto_show', methods: ['GET'])]
    public function show(Projeto $projeto): Response
    {
        return $this->render('projeto/show.html.twig', [
            'projeto' => $projeto,
        ]);
    }

    #[Route('/{id}/excluir', name: 'app_projeto_delete', methods: ['POST'])]
    public function delete(Projeto $projeto, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_projeto_'.$projeto->getId(), (string) $request->request->get('_token'))) {
            $em->remove($projeto);
            $em->flush();
            $this->addFlash('success', 'Projeto excluído com sucesso.');
        } else {
            $this->addFlash('error', 'Falha de segurança ao excluir projeto (CSRF inválido).');
        }

        return $this->redirectToRoute('app_projeto_index');
    }
}
