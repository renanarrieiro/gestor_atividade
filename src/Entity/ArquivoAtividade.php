<?php

namespace App\Entity;

use App\Repository\ArquivoAtividadeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArquivoAtividadeRepository::class)]
class ArquivoAtividade
{
    public const TIPO_TECNICA = 'TECNICA';
    public const TIPO_FUNCIONAL = 'FUNCIONAL';
    public const TIPO_TESTE = 'TESTE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $tipo = self::TIPO_TECNICA;

    #[ORM\Column(length: 255)]
    private string $nomeOriginal = '';

    #[ORM\Column(length: 255)]
    private string $caminho = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $criadoEm;

    #[ORM\ManyToOne(inversedBy: 'arquivos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Atividade $atividade = null;

    public function __construct()
    {
        $this->criadoEm = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getNomeOriginal(): string
    {
        return $this->nomeOriginal;
    }

    public function setNomeOriginal(string $nomeOriginal): self
    {
        $this->nomeOriginal = $nomeOriginal;

        return $this;
    }

    public function getCaminho(): string
    {
        return $this->caminho;
    }

    public function setCaminho(string $caminho): self
    {
        $this->caminho = $caminho;

        return $this;
    }

    public function getCriadoEm(): \DateTimeImmutable
    {
        return $this->criadoEm;
    }

    public function getAtividade(): ?Atividade
    {
        return $this->atividade;
    }

    public function setAtividade(?Atividade $atividade): self
    {
        $this->atividade = $atividade;

        return $this;
    }
}
