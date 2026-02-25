<?php

namespace App\Repository;

use App\Entity\ArquivoAtividade;
use Doctrine\Persistence\ManagerRegistry;

class ArquivoAtividadeRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArquivoAtividade::class);
    }
}
