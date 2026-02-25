<?php

namespace App\Entity;

use App\Repository\SapRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SapRequestRepository::class)]
#[ORM\Table(name: 'sap_request')]
class SapRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private string $tipo = '';

    #[ORM\Column(length: 30)]
    private string $numero = '';

    #[ORM\Column(type: 'text')]
    private string $descricao = '';

    #[ORM\Column(length: 30)]
    private string $modulo = '';

    #[ORM\Column(length: 60)]
    private string $usuario = '';

    #[ORM\Column(length: 40)]
    private string $status = '';

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $dataRequest;

    #[ORM\ManyToOne(inversedBy: 'requests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Atividade $atividade = null;

    public function __construct()
    {
        $this->dataRequest = new \DateTimeImmutable();
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

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getModulo(): string
    {
        return $this->modulo;
    }

    public function setModulo(string $modulo): self
    {
        $this->modulo = $modulo;

        return $this;
    }

    public function getUsuario(): string
    {
        return $this->usuario;
    }

    public function setUsuario(string $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDataRequest(): \DateTimeImmutable
    {
        return $this->dataRequest;
    }

    public function setDataRequest(\DateTimeImmutable $dataRequest): self
    {
        $this->dataRequest = $dataRequest;

        return $this;
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
