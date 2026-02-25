<?php

namespace App\Repository;

use App\Entity\Projeto;
use Doctrine\Persistence\ManagerRegistry;

class ProjetoRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projeto::class);
    }
}
