<?php

namespace Tycoon\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Tycoon\ApiBundle\Entity\UserRepository")
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
     * @ORM\Column(name="facebook_id", type="integer")
     */
    private $facebookId;
    
    /**
     * @ORM\OneToMany(targetEntity="UserRestaurant", mappedBy="users")
     */
    protected $userRestaurants;

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
     * Add userRestaurants
     *
     * @param \Tycoon\ApiBundle\Entity\UserRestaurant $userRestaurants
     * @return User
     */
    public function addUserRestaurant(\Tycoon\ApiBundle\Entity\UserRestaurant $userRestaurants)
    {
        $this->userRestaurants[] = $userRestaurants;

        return $this;
    }

    /**
     * Remove userRestaurants
     *
     * @param \Tycoon\ApiBundle\Entity\UserRestaurant $userRestaurants
     */
    public function removeUserRestaurant(\Tycoon\ApiBundle\Entity\UserRestaurant $userRestaurants)
    {
        $this->userRestaurants->removeElement($userRestaurants);
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

}
