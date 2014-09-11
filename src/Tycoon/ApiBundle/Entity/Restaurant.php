<?php

namespace Tycoon\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Restaurant
 *
 * @ORM\Table(name="restaurant")
 * @ORM\Entity(repositoryClass="Tycoon\ApiBundle\Entity\RestaurantRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Restaurant
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
     * @var string
     *
     * @ORM\Column(name="justeat_id", type="integer")
     */
    private $justeatId;
    
    /**
     * @ORM\ManyToMany(targetEntity="Postcode", inversedBy="restaurants")
     */
    protected $postcodes;
    
    /**
     * @ORM\OneToMany(targetEntity="UserRestaurant", mappedBy="restaurants")
     */
    protected $userRestaurants;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=255)
     */
    private $logo;

    /**
     * @var decimal
     *
     * @ORM\Column(name="price", type="decimal", scale=2)
     */
    private $price = 0;

    /**
     * @var decimal
     *
     * @ORM\Column(name="score", type="decimal", scale=2)
     */
    private $score = 0;
    
    /**
     * Variable used to calculate price
     */
    private $multiplier = 10;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float")
     */
    private $latitude = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float")
     */
    private $longitude = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="refreshed_at", type="datetime")
     */
    private $refreshedAt;
    
    private $refreshingDays = 1;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->postcodes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set justeatId
     *
     * @param integer $justeatId
     * @return Restaurant
     */
    public function setJusteatId($justeatId)
    {
        $this->justeatId = $justeatId;

        return $this;
    }

    /**
     * Get justeatId
     *
     * @return integer 
     */
    public function getJusteatId()
    {
        return $this->justeatId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Restaurant
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return Restaurant
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return Restaurant
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string 
     */
    public function getPrice()
    {
        if (empty($this->price) || $this->getRefreshedAt()->format('Y-m-d') < date('Y-m-d', time()-$this->getRefreshingTime())) {
        // Last refreshed more than 1 day ago: update Price
            $this->price = $this->score*$this->multiplier;
            
            $this->initRefreshedAt();
        }
        
        return $this->price;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     * @return Restaurant
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return Restaurant
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Restaurant
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
     * @return Restaurant
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
    
    /**
     * @ORM\PrePersist
     */
    public function initCreatedAt() {
        $this->createdAt = new \DateTime();
    }
    
    /**
     * @ORM\PrePersist
     */
    public function initRefreshedAt() {
        $this->refreshedAt = new \DateTime();
    }

    /**
     * Add postcodes
     *
     * @param \Tycoon\ApiBundle\Entity\Postcode $postcodes
     * @return Restaurant
     */
    public function addPostcode(\Tycoon\ApiBundle\Entity\Postcode $postcode)
    {
        $this->postcodes->removeElement($postcode);
        $this->postcodes[] = $postcode;

        return $this;
    }

    /**
     * Remove postcodes
     *
     * @param \Tycoon\ApiBundle\Entity\Postcode $postcodes
     */
    public function removePostcode(\Tycoon\ApiBundle\Entity\Postcode $postcode)
    {
        $this->postcodes->removeElement($postcode);
    }

    /**
     * Get postcodes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPostcodes()
    {
        return $this->postcodes;
    }

    /**
     * Set score
     *
     * @param string $score
     * @return Restaurant
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return string 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Add userRestaurants
     *
     * @param \Tycoon\ApiBundle\Entity\UserRestaurant $userRestaurants
     * @return Restaurant
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
    
    /**
     * Get refreshing time
     * 
     * @return int
     */
    public function getRefreshingTime() {
        return $this->refreshingDays*24*60*60;
    }

}
