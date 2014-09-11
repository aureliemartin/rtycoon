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
     * @var decimal
     *
     * @ORM\Column(name="initial_price", type="decimal", scale=2)
     */
    private $initialPrice = 0;


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
        $this->initialPrice = $restaurant->getPrice();

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

    /**
     * Set initialPrice
     *
     * @param string $initialPrice
     * @return UserRestaurant
     */
    public function setInitialPrice($initialPrice)
    {
        $this->initialPrice = $initialPrice;

        return $this;
    }

    /**
     * Get initialPrice
     *
     * @return string 
     */
    public function getInitialPrice()
    {
        return $this->initialPrice;
    }
}
