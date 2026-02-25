<?php

namespace App\Entity;

use App\Repository\FeriadoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeriadoRepository::class)]
#[ORM\Table(name: 'feriado')]
#[ORM\UniqueConstraint(name: 'uniq_feriado_data', columns: ['data'])]
class Feriado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $data;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $descricao = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $criadoEm;

    public function __construct()
    {
        $this->data = new \DateTimeImmutable('today');
        $this->criadoEm = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): \DateTimeImmutable
    {
        return $this->data;
    }

    public function setData(\DateTimeImmutable $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): self
    {
        $descricao = $descricao !== null ? trim($descricao) : null;
        $this->descricao = $descricao === '' ? null : $descricao;

        return $this;
    }

    public function getCriadoEm(): \DateTimeImmutable
    {
        return $this->criadoEm;
    }
}
