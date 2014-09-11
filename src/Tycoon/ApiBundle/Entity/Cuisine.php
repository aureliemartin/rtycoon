<?php

namespace Tycoon\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cuisine
 *
 * @ORM\Table(name="cuisine")
 * @ORM\Entity(repositoryClass="Tycoon\ApiBundle\Entity\CuisineRepository")
 */
class Cuisine
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
     * @ORM\ManyToMany(targetEntity="Restaurant", mappedBy="cuisines")
     */
    protected $restaurants;

    /**
     * @var string
     *
     * @ORM\Column(name="justeat_id", type="integer")
     */
    private $justeatId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


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
     * Set name
     *
     * @param string $name
     * @return Cuisine
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
     * Constructor
     */
    public function __construct()
    {
        $this->restaurants = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add restaurants
     *
     * @param \Tycoon\ApiBundle\Entity\Restaurant $restaurant
     * @return Cuisine
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
    public function removeRestaurant(\Tycoon\ApiBundle\Entity\Restaurant $restaurants)
    {
        $this->restaurants->removeElement($restaurants);
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
     * Set justeatId
     *
     * @param integer $justeatId
     * @return Cuisine
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
}
