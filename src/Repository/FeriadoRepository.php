<?php

namespace App\Repository;

use App\Entity\Feriado;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<Feriado>
 */
class FeriadoRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feriado::class);
    }

    /**
     * @return array<int, Feriado>
     */
    public function findByMesAno(int $mes, int $ano): array
    {
        $inicio = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $ano, $mes));
        $fim = $inicio->modify('last day of this month')->setTime(23, 59, 59);

        return $this->createQueryBuilder('f')
            ->andWhere('f.data BETWEEN :inicio AND :fim')
            ->setParameter('inicio', $inicio->setTime(0, 0, 0))
            ->setParameter('fim', $fim)
            ->orderBy('f.data', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
