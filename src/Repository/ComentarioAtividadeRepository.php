<?php

namespace App\Repository;

use App\Entity\ComentarioAtividade;
use Doctrine\Persistence\ManagerRegistry;

class ComentarioAtividadeRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComentarioAtividade::class);
    }
}
