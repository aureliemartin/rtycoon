<?php

namespace Tycoon\ApiBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRestaurantRepository
 */
class UserRestaurantRepository extends EntityRepository {
    
    public function getByUserAndRestaurant($user, $restaurant) {
        $query = $this->createQueryBuilder('ur')
            ->join('ur.user', 'u')
            ->addSelect('u')
            ->join('ur.restaurant', 'r')
            ->addSelect('r')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->andWhere('r.id = :restaurantId')
            ->setParameter('restaurantId', $restaurant->getId())
            ->getQuery();
        
        $result = $query->getOneOrNullResult();
        
        return $result;
    }
    
}
