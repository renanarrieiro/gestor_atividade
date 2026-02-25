<?php

namespace App\Repository;

use App\Entity\SapRequest;
use Doctrine\Persistence\ManagerRegistry;

class SapRequestRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SapRequest::class);
    }

    public function buscarComFiltros(array $filtros): array
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.atividade', 'a')
            ->join('a.projeto', 'p')
            ->addSelect('a', 'p')
            ->orderBy('r.dataRequest', 'DESC');

        if (!empty($filtros['projeto'])) {
            $qb->andWhere('p.id = :projeto')->setParameter('projeto', (int) $filtros['projeto']);
        }

        if (!empty($filtros['atividade'])) {
            $qb->andWhere('a.id = :atividade')->setParameter('atividade', (int) $filtros['atividade']);
        }

        foreach (['tipo', 'modulo', 'status', 'numero'] as $campo) {
            if (!empty($filtros[$campo])) {
                $qb->andWhere(sprintf('LOWER(r.%s) LIKE :%s', $campo, $campo))
                    ->setParameter($campo, '%'.mb_strtolower(trim((string) $filtros[$campo])).'%');
            }
        }

        return $qb->getQuery()->getResult();
    }
}
