<?php

namespace Tycoon\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Tycoon\ApiBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User
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
     * @var integer
     *
     * @ORM\Column(name="facebook_id", type="string", length=50)
     */
    private $facebookId;
    
    /**
     * @ORM\OneToMany(targetEntity="UserRestaurant", mappedBy="user")
     */
    protected $userRestaurants;
    private $restaurantIds = array();

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userRestaurants = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set facebookId
     *
     * @param integer $facebookId
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return integer 
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function initCreatedAt() {
        $this->createdAt = new \DateTime();
    }

    /**
     * Add userRestaurants
     *
     * @param \Tycoon\ApiBundle\Entity\UserRestaurant $userRestaurant
     * @return User
     */
    public function addUserRestaurant(\Tycoon\ApiBundle\Entity\UserRestaurant $userRestaurant)
    {
        $this->userRestaurants[] = $userRestaurant;
        $this->restaurantIds[] = $userRestaurant->getRestaurant()->getId();
        
        return $this;
    }

    /**
     * Remove userRestaurants
     *
     * @param \Tycoon\ApiBundle\Entity\UserRestaurant $userRestaurant
     */
    public function removeUserRestaurant(\Tycoon\ApiBundle\Entity\UserRestaurant $userRestaurant)
    {
        $this->userRestaurants->removeElement($userRestaurant);
        $this->restaurantIds = array_diff($this->restaurantIds, array($userRestaurant->getRestaurant()->getId()));
    }

    /**
     * Get userRestaurants
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserRestaurants()
    {
        return $this->userRestaurants;
    }
    
    /**
     * Does the user owns this restaurant
     * 
     * @param \Tycoon\ApiBundle\Entity\Restaurant $restaurant
     * 
     * @return boolean
     */
    public function ownRestaurant($restaurant) {
        if (empty($this->restaurantIds)) {
            foreach($this->getUserRestaurants() as $userRestaurant) {
                $this->restaurantIds[] = $userRestaurant->getRestaurant()->getId();
            }
        }
        
        return in_array($restaurant->getId(), $this->restaurantIds);
    }

}
