<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArquivoUploader
{
    public function __construct(private readonly string $baseDir)
    {
    }

    public function upload(UploadedFile $file, string $tipo): string
    {
        $tipoNormalizado = strtolower($tipo);
        $diretorio = match ($tipoNormalizado) {
            'funcional' => 'funcional',
            'teste' => 'teste',
            default => 'tecnica',
        };
        $destino = rtrim($this->baseDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$diretorio;

        if (!is_dir($destino) && !mkdir($destino, 0775, true) && !is_dir($destino)) {
            throw new \RuntimeException(sprintf('Nao foi possivel criar o diretorio de upload: %s', $destino));
        }

        $extensao = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $nome = sprintf('%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), $extensao);

        $file->move($destino, $nome);

        return $diretorio.'/'.$nome;
    }
}
