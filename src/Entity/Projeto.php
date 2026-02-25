<?php

namespace App\Entity;

use App\Repository\ProjetoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetoRepository::class)]
class Projeto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $nome = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $criadoEm;

    #[ORM\OneToMany(mappedBy: 'projeto', targetEntity: Atividade::class, orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'DESC'])]
    private Collection $atividades;

    public function __construct()
    {
        $this->criadoEm = new \DateTimeImmutable();
        $this->atividades = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;

        return $this;
    }

    public function getCriadoEm(): \DateTimeImmutable
    {
        return $this->criadoEm;
    }

    public function getAtividades(): Collection
    {
        return $this->atividades;
    }

    public function addAtividade(Atividade $atividade): self
    {
        if (!$this->atividades->contains($atividade)) {
            $this->atividades->add($atividade);
            $atividade->setProjeto($this);
        }

        return $this;
    }

    public function removeAtividade(Atividade $atividade): self
    {
        if ($this->atividades->removeElement($atividade) && $atividade->getProjeto() === $this) {
            $atividade->setProjeto(null);
        }

        return $this;
    }
}
