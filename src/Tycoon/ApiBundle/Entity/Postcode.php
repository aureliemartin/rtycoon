<?php

namespace Tycoon\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Postcode
 *
 * @ORM\Table(name="postcode")
 * @ORM\Entity(repositoryClass="Tycoon\ApiBundle\Entity\PostcodeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Postcode
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
     * @ORM\ManyToMany(targetEntity="Restaurant", mappedBy="postcodes")
     */
    protected $restaurants;

    /**
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=50)
     */
    private $postcode;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="refreshed_at", type="datetime", nullable=true)
     */
    private $refreshedAt;
    
    private $refreshingDays = 30;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->restaurants = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set postcode
     *
     * @param string $postcode
     * @return Postcode
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get postcode
     *
     * @return string 
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Postcode
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
     * Set refreshedAt
     *
     * @param \DateTime $refreshedAt
     * @return Postcode
     */
    public function setRefreshedAt($refreshedAt)
    {
        $this->refreshedAt = $refreshedAt;

        return $this;
    }

    /**
     * Get refreshedAt
     *
     * @return \DateTime 
     */
    public function getRefreshedAt()
    {
        return $this->refreshedAt;
    }
    
    public function initRefreshedAt() {
        $this->refreshedAt = new \DateTime();
    }
    
    /**
     * @ORM\PrePersist
     */
    public function initCreatedAt() {
        $this->createdAt = new \DateTime();
    }

    /**
     * Add restaurants
     *
     * @param \Tycoon\ApiBundle\Entity\Restaurant $restaurants
     * @return Postcode
     */
    public function addRestaurant(\Tycoon\ApiBundle\Entity\Restaurant $restaurant)
    {
        $this->restaurants->removeElement($restaurant);
        $this->restaurants[] = $restaurant;

        return $this;
    }

    /**
     * Remove restaurants
     *
     * @param \Tycoon\ApiBundle\Entity\Restaurant $restaurants
     */
    public function removeRestaurant(\Tycoon\ApiBundle\Entity\Restaurant $restaurant)
    {
        $this->restaurants->removeElement($restaurant);
    }

    /**
     * Get restaurants
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRestaurants()
    {
        return $this->restaurants;
    }
    
    /**
     * Get refreshing time
     * 
     * @return int
     */
    public function getRefreshingTime() {
        return $this->refreshingDays*24*60*60;
    }
}
