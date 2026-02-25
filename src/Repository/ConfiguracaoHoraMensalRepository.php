<?php

namespace App\Repository;

use App\Entity\ConfiguracaoHoraMensal;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<ConfiguracaoHoraMensal>
 */
class ConfiguracaoHoraMensalRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfiguracaoHoraMensal::class);
    }

    public function findOneByMesAno(int $mes, int $ano): ?ConfiguracaoHoraMensal
    {
        return $this->findOneBy([
            'mes' => $mes,
            'ano' => $ano,
        ]);
    }
}
