<?php

namespace Tycoon\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserRestaurant
 *
 * @ORM\Table(name="user_restaurant")
 * @ORM\Entity(repositoryClass="Tycoon\ApiBundle\Entity\UserRestaurantRepository")
 */
class UserRestaurant
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userRestaurants")
     **/
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Restaurant", inversedBy="userRestaurants")
     **/
    private $restaurant;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param \Tycoon\ApiBundle\Entity\User $user
     * @return UserRestaurant
     */
    public function setUser(\Tycoon\ApiBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Tycoon\ApiBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set restaurant
     *
     * @param \Tycoon\ApiBundle\Entity\Restaurant $restaurant
     * @return UserRestaurant
     */
    public function setRestaurant(\Tycoon\ApiBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * Get restaurant
     *
     * @return \Tycoon\ApiBundle\Entity\Restaurant 
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }
}
