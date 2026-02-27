<?php

namespace App\Controller;

use App\Entity\ArquivoAtividade;
use App\Entity\Atividade;
use App\Entity\ComentarioAtividade;
use App\Entity\Projeto;
use App\Entity\SapRequest;
use App\Form\ArquivoAtividadeType;
use App\Form\AtividadeType;
use App\Form\ComentarioAtividadeType;
use App\Form\SapRequestType;
use App\Repository\ComentarioAtividadeRepository;
use App\Repository\SapRequestRepository;
use App\Service\ArquivoUploader;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/atividades')]
class AtividadeController extends AbstractController
{
    #[Route('/nova/{projeto}', name: 'app_atividade_new', methods: ['GET', 'POST'])]
    public function new(Projeto $projeto, Request $request, EntityManagerInterface $em): Response
    {
        $atividade = new Atividade();
        $atividade->setProjeto($projeto);

        $form = $this->createForm(AtividadeType::class, $atividade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($atividade);
                $em->flush();
                $this->addFlash('success', 'Atividade criada com sucesso.');

                return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId()]);
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'Já existe uma atividade com este ID Externo neste projeto.');
            }
        }

        return $this->render('atividade/new.html.twig', [
            'projeto' => $projeto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/editar', name: 'app_atividade_edit', methods: ['GET', 'POST'])]
    public function edit(Atividade $atividade, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AtividadeType::class, $atividade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Atividade atualizada com sucesso.');

                return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId()]);
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'Já existe uma atividade com este ID Externo neste projeto.');
            }
        }

        return $this->render('atividade/edit.html.twig', [
            'atividade' => $atividade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_atividade_show', methods: ['GET', 'POST'])]
    public function show(
        Atividade $atividade,
        Request $request,
        EntityManagerInterface $em,
        SapRequestRepository $sapRequestRepository
    ): Response
    {
        $sapRequest = new SapRequest();
        $formRequest = $this->createForm(SapRequestType::class, $sapRequest, [
            'action' => $this->generateUrl('app_atividade_show', ['id' => $atividade->getId()]),
        ]);
        $formRequest->handleRequest($request);

        if ($formRequest->isSubmitted() && $formRequest->isValid()) {
            $sapRequest->setAtividade($atividade);
            $duplicado = $sapRequestRepository->findOneBy([
                'atividade' => $atividade,
                'tipo' => $sapRequest->getTipo(),
                'numero' => $sapRequest->getNumero(),
                'descricao' => $sapRequest->getDescricao(),
                'modulo' => $sapRequest->getModulo(),
                'usuario' => $sapRequest->getUsuario(),
                'status' => $sapRequest->getStatus(),
                'dataRequest' => $sapRequest->getDataRequest(),
            ]);

            if ($duplicado) {
                $this->addFlash('error', 'Request duplicada detectada. Registro ignorado.');
            } else {
                $em->persist($sapRequest);
                $em->flush();
            }

            return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId()]);
        }

        $comentario = new ComentarioAtividade();
        $formComentario = $this->createForm(ComentarioAtividadeType::class, $comentario, [
            'action' => $this->generateUrl('app_atividade_comentario_add', ['id' => $atividade->getId()]),
        ]);

        $arquivoTecnica = new \App\Entity\ArquivoAtividade();
        $arquivoTecnica->setTipo(\App\Entity\ArquivoAtividade::TIPO_TECNICA);
        $formTecnica = $this->createForm(ArquivoAtividadeType::class, $arquivoTecnica, [
            'action' => $this->generateUrl('app_atividade_upload', ['id' => $atividade->getId(), 'tipo' => 'TECNICA']),
        ]);

        $arquivoFuncional = new \App\Entity\ArquivoAtividade();
        $arquivoFuncional->setTipo(\App\Entity\ArquivoAtividade::TIPO_FUNCIONAL);
        $formFuncional = $this->createForm(ArquivoAtividadeType::class, $arquivoFuncional, [
            'action' => $this->generateUrl('app_atividade_upload', ['id' => $atividade->getId(), 'tipo' => 'FUNCIONAL']),
        ]);

        $arquivoTeste = new \App\Entity\ArquivoAtividade();
        $arquivoTeste->setTipo(\App\Entity\ArquivoAtividade::TIPO_TESTE);
        $formTeste = $this->createForm(ArquivoAtividadeType::class, $arquivoTeste, [
            'action' => $this->generateUrl('app_atividade_upload', ['id' => $atividade->getId(), 'tipo' => 'TESTE']),
        ]);

        return $this->render('atividade/show.html.twig', [
            'atividade' => $atividade,
            'formRequest' => $formRequest,
            'formComentario' => $formComentario,
            'formTecnica' => $formTecnica,
            'formFuncional' => $formFuncional,
            'formTeste' => $formTeste,
        ]);
    }

    #[Route('/{id}/upload/{tipo}', name: 'app_atividade_upload', methods: ['POST'])]
    public function upload(
        Atividade $atividade,
        string $tipo,
        Request $request,
        EntityManagerInterface $em,
        ArquivoUploader $uploader
    ): Response {
        $arquivo = new \App\Entity\ArquivoAtividade();
        $arquivo->setTipo(strtoupper($tipo));

        $form = $this->createForm(ArquivoAtividadeType::class, $arquivo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('arquivo')->getData();
            if ($file) {
                try {
                    $path = $uploader->upload($file, $tipo);
                    $arquivo->setAtividade($atividade);
                    $arquivo->setNomeOriginal($file->getClientOriginalName());
                    $arquivo->setCaminho($path);
                    $em->persist($arquivo);
                    $em->flush();
                    $this->addFlash('success', 'Anexo enviado com sucesso.');
                } catch (\RuntimeException|\Symfony\Component\HttpFoundation\File\Exception\FileException) {
                    $this->addFlash('error', 'Falha ao salvar o arquivo no servidor. Tente novamente.');
                }
            }
        } else {
            $this->addFlash('error', 'Não foi possível enviar o anexo. Verifique os campos obrigatórios.');
        }

        return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId()]);
    }

    #[Route('/{id}/comentarios/adicionar', name: 'app_atividade_comentario_add', methods: ['POST'])]
    public function addComentario(
        Atividade $atividade,
        Request $request,
        EntityManagerInterface $em,
        ComentarioAtividadeRepository $comentarioRepository
    ): Response {
        $comentario = new ComentarioAtividade();
        $formComentario = $this->createForm(ComentarioAtividadeType::class, $comentario);
        $formComentario->handleRequest($request);

        if ($formComentario->isSubmitted() && $formComentario->isValid()) {
            $comentario->setAtividade($atividade);
            $duplicado = $comentarioRepository->findOneBy([
                'atividade' => $atividade,
                'texto' => $comentario->getTexto(),
            ]);

            if ($duplicado) {
                $this->addFlash('error', 'Comentario duplicado detectado. Registro ignorado.');
            } else {
                $em->persist($comentario);
                $em->flush();
                $this->addFlash('success', 'Comentário adicionado com sucesso.');
            }
        } else {
            $this->addFlash('error', 'Não foi possível adicionar o comentário. Verifique o preenchimento.');
        }

        return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId(), '_fragment' => 'comentarios']);
    }

    #[Route('/{atividade}/anexos/{arquivo}/excluir', name: 'app_atividade_arquivo_delete', methods: ['POST'])]
    public function deleteArquivo(
        Atividade $atividade,
        ArquivoAtividade $arquivo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('delete_arquivo_'.$arquivo->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Falha de segurança ao excluir anexo (CSRF inválido).');

            return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId()]);
        }

        if ($arquivo->getAtividade()?->getId() !== $atividade->getId()) {
            $this->addFlash('error', 'Anexo não pertence a esta atividade.');

            return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId()]);
        }

        $caminhoCompleto = $this->getParameter('kernel.project_dir').'/var/uploads/'.$arquivo->getCaminho();
        if (is_file($caminhoCompleto)) {
            @unlink($caminhoCompleto);
        }

        $em->remove($arquivo);
        $em->flush();
        $this->addFlash('success', 'Anexo excluído com sucesso.');

        return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId()]);
    }

    #[Route('/{atividade}/comentarios/{comentario}/excluir', name: 'app_atividade_comentario_delete', methods: ['POST'])]
    public function deleteComentario(
        Atividade $atividade,
        ComentarioAtividade $comentario,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('delete_comentario_'.$comentario->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Falha de segurança ao excluir comentário (CSRF inválido).');

            return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId(), '_fragment' => 'comentarios']);
        }

        if ($comentario->getAtividade()?->getId() !== $atividade->getId()) {
            $this->addFlash('error', 'Comentário não pertence a esta atividade.');

            return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId(), '_fragment' => 'comentarios']);
        }

        $em->remove($comentario);
        $em->flush();
        $this->addFlash('success', 'Comentário excluído com sucesso.');

        return $this->redirectToRoute('app_atividade_show', ['id' => $atividade->getId(), '_fragment' => 'comentarios']);
    }

    #[Route('/{id}/excluir', name: 'app_atividade_delete', methods: ['POST'])]
    public function delete(Atividade $atividade, Request $request, EntityManagerInterface $em): Response
    {
        $projetoId = $atividade->getProjeto()?->getId();

        if ($this->isCsrfTokenValid('delete_atividade_'.$atividade->getId(), (string) $request->request->get('_token'))) {
            $em->remove($atividade);
            $em->flush();
            $this->addFlash('success', 'Atividade excluída com sucesso.');
        } else {
            $this->addFlash('error', 'Falha de segurança ao excluir atividade (CSRF inválido).');
        }

        if ($projetoId) {
            return $this->redirectToRoute('app_projeto_show', ['id' => $projetoId]);
        }

        return $this->redirectToRoute('app_projeto_index');
    }
}
