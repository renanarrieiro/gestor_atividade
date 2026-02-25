<?php

namespace App\Entity;

use App\Repository\AtividadeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AtividadeRepository::class)]
#[ORM\Table(name: 'atividade', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_atividade_projeto_idexterno', columns: ['projeto_id', 'id_externo'])])]
#[UniqueEntity(fields: ['projeto', 'idExterno'], message: 'Já existe uma atividade com este ID Externo neste projeto.')]
class Atividade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private string $idExterno = '';

    #[ORM\Column(type: 'text')]
    private string $descricao = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $criadoEm;

    #[ORM\ManyToOne(inversedBy: 'atividades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Projeto $projeto = null;

    #[ORM\OneToMany(mappedBy: 'atividade', targetEntity: SapRequest::class, orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['dataRequest' => 'DESC'])]
    private Collection $requests;

    #[ORM\OneToMany(mappedBy: 'atividade', targetEntity: ArquivoAtividade::class, orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['criadoEm' => 'DESC'])]
    private Collection $arquivos;

    #[ORM\OneToMany(mappedBy: 'atividade', targetEntity: ComentarioAtividade::class, orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['criadoEm' => 'DESC'])]
    private Collection $comentarios;

    public function __construct()
    {
        $this->criadoEm = new \DateTimeImmutable();
        $this->requests = new ArrayCollection();
        $this->arquivos = new ArrayCollection();
        $this->comentarios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdExterno(): string
    {
        return $this->idExterno;
    }

    public function setIdExterno(string $idExterno): self
    {
        $this->idExterno = $idExterno;

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

    public function getCriadoEm(): \DateTimeImmutable
    {
        return $this->criadoEm;
    }

    public function getProjeto(): ?Projeto
    {
        return $this->projeto;
    }

    public function setProjeto(?Projeto $projeto): self
    {
        $this->projeto = $projeto;

        return $this;
    }

    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(SapRequest $request): self
    {
        if (!$this->requests->contains($request)) {
            $this->requests->add($request);
            $request->setAtividade($this);
        }

        return $this;
    }

    public function getArquivos(): Collection
    {
        return $this->arquivos;
    }

    public function addArquivo(ArquivoAtividade $arquivo): self
    {
        if (!$this->arquivos->contains($arquivo)) {
            $this->arquivos->add($arquivo);
            $arquivo->setAtividade($this);
        }

        return $this;
    }

    public function getComentarios(): Collection
    {
        return $this->comentarios;
    }

    public function addComentario(ComentarioAtividade $comentario): self
    {
        if (!$this->comentarios->contains($comentario)) {
            $this->comentarios->add($comentario);
            $comentario->setAtividade($this);
        }

        return $this;
    }
}
