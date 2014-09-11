<?php

namespace Tycoon\ApiBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository {
    
    public function getRanked() {
        $query = $this->createQueryBuilder('u')
            ->orderBy('u.rank', 'ASC')
            ->setMaxResults(10)
            ->getQuery();
        
        $result = $query->getResult();
        
        return $result;
    }
}
