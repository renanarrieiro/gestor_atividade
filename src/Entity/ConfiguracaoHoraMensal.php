<?php

namespace App\Entity;

use App\Repository\ConfiguracaoHoraMensalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfiguracaoHoraMensalRepository::class)]
#[ORM\Table(name: 'configuracao_hora_mensal')]
#[ORM\UniqueConstraint(name: 'uniq_configuracao_hora_mensal', columns: ['ano', 'mes'])]
class ConfiguracaoHoraMensal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'smallint')]
    private int $ano;

    #[ORM\Column(type: 'smallint')]
    private int $mes;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $taxaHora = '0.00';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $atualizadoEm;

    public function __construct()
    {
        $this->ano = (int) date('Y');
        $this->mes = (int) date('m');
        $this->atualizadoEm = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAno(): int
    {
        return $this->ano;
    }

    public function setAno(int $ano): self
    {
        $this->ano = $ano;

        return $this;
    }

    public function getMes(): int
    {
        return $this->mes;
    }

    public function setMes(int $mes): self
    {
        $this->mes = $mes;

        return $this;
    }

    public function getTaxaHora(): float
    {
        return (float) $this->taxaHora;
    }

    public function setTaxaHora(float $taxaHora): self
    {
        $this->taxaHora = number_format(max(0.0, $taxaHora), 2, '.', '');
        $this->atualizadoEm = new \DateTimeImmutable();

        return $this;
    }

    public function getAtualizadoEm(): \DateTimeImmutable
    {
        return $this->atualizadoEm;
    }
}
