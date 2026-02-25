<?php

namespace App\Controller;

use App\Entity\ArquivoAtividade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class ArquivoController extends AbstractController
{
    #[Route('/arquivo/{id}/download', name: 'app_arquivo_download', methods: ['GET'])]
    public function download(ArquivoAtividade $arquivo): BinaryFileResponse
    {
        $caminho = $this->getParameter('kernel.project_dir').'/var/uploads/'.$arquivo->getCaminho();

        $response = new BinaryFileResponse($caminho);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $arquivo->getNomeOriginal());

        return $response;
    }
}
