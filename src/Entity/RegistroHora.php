<?php

namespace App\Entity;

use App\Repository\RegistroHoraRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegistroHoraRepository::class)]
#[ORM\Table(name: 'registro_hora')]
#[ORM\UniqueConstraint(name: 'uniq_registro_hora_data', columns: ['data'])]
class RegistroHora
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $data;

    #[ORM\Column(type: 'integer')]
    private int $horasTrabalhadas = 0;

    #[ORM\Column(type: 'text')]
    private string $comentarios = '';

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

    public function getHorasTrabalhadas(): int
    {
        return $this->horasTrabalhadas;
    }

    public function setHorasTrabalhadas(int $horasTrabalhadas): self
    {
        $this->horasTrabalhadas = $horasTrabalhadas;

        return $this;
    }

    public function getComentarios(): string
    {
        return $this->comentarios;
    }

    public function setComentarios(string $comentarios): self
    {
        $this->comentarios = $comentarios;

        return $this;
    }

    public function getCriadoEm(): \DateTimeImmutable
    {
        return $this->criadoEm;
    }
}
