<?php

namespace App\Repository;

use App\Entity\RegistroHora;
use Doctrine\Persistence\ManagerRegistry;

class RegistroHoraRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegistroHora::class);
    }

    /**
     * @return array<int, RegistroHora>
     */
    public function findByMesAno(int $mes, int $ano): array
    {
        $inicio = new \DateTimeImmutable(sprintf('%04d-%02d-01', $ano, $mes));
        $fim = $inicio->modify('last day of this month');

        return $this->createQueryBuilder('r')
            ->andWhere('r.data >= :inicio')
            ->andWhere('r.data <= :fim')
            ->setParameter('inicio', $inicio)
            ->setParameter('fim', $fim)
            ->orderBy('r.data', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<int, RegistroHora> $registros
     */
    public function somarHoras(array $registros): int
    {
        $total = 0;

        foreach ($registros as $registro) {
            $total += $registro->getHorasTrabalhadas();
        }

        return $total;
    }
}
