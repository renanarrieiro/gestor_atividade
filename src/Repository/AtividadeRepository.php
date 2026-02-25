<?php

namespace App\Repository;

use App\Entity\Atividade;
use Doctrine\Persistence\ManagerRegistry;

class AtividadeRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Atividade::class);
    }

    /**
     * @return array<int, Atividade>
     */
    public function findComRequestsParaFiltro(): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.requests', 'r')
            ->addSelect('p')
            ->innerJoin('a.projeto', 'p')
            ->distinct()
            ->orderBy('p.nome', 'ASC')
            ->addOrderBy('a.idExterno', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
