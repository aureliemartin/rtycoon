<?php

namespace Tycoon\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserRestaurant
 *
 * @ORM\Table(name="user_restaurant")
 * @ORM\Entity(repositoryClass="Tycoon\ApiBundle\Entity\UserRestaurantRepository")
 * @ORM\HasLifecycleCallbacks()
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
     * @var decimal
     *
     * @ORM\Column(name="last_score", type="decimal", scale=2)
     */
    private $lastScore = 0;

    /**
     * @var decimal
     *
     * @ORM\Column(name="profit", type="decimal", scale=2)
     */
    private $profit = 0;

    /**
     * @var decimal
     *
     * @ORM\Column(name="total_profit", type="decimal", scale=2)
     */
    private $totalProfit = 0;

    /**
     * @var decimal
     *
     * @ORM\Column(name="cost", type="decimal", scale=2)
     */
    private $cost = 0;

    /**
     * @var decimal
     *
     * @ORM\Column(name="expected_cost", type="decimal", scale=2)
     */
    private $expectedCost = 0;

    /**
     * @var decimal
     *
     * @ORM\Column(name="total_cost", type="decimal", scale=2)
     */
    private $totalCost = 0;

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
     * @var \DateTime
     *
     * @ORM\Column(name="last_connection_at", type="datetime")
     */
    private $lastConnectionAt;
    
    /**
     * Variable used to calculate profits
     */
    private $minScore = 0.5;
    private $profitMultiplier = 220.2;
    private $costMultiplier = 156.1;


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
     * @ORM\PrePersist
     */
    public function initLastConnectionAt() {
        $this->lastConnectionAt = new \DateTime();
    }

    /**
     * Set lastScore
     *
     * @param string $lastScore
     * @return UserRestaurant
     */
    public function setLastScore($lastScore)
    {
        $this->lastScore = $lastScore;

        return $this;
    }

    /**
     * Get lastScore
     *
     * @return string 
     */
    public function getLastScore()
    {
        return $this->lastScore;
    }
    
    /**
     * Get refreshing time
     * 
     * @return int
     */
    public function getRefreshingTime() {
        return $this->refreshingDays*24*60*60;
    }
    
    public function setProfitAndCost() {
        if ($this->profit == 0 || $this->getRefreshedAt()->format('Y-m-d') <= date('Y-m-d', time()-$this->getRefreshingTime())) {
        // Last refreshed more than 1 day ago: update Profit and Cost
            
            // Profit
            $lastScore = $this->lastScore;
            $currentScore = $this->restaurant->getScore();
            $score = $currentScore-$lastScore;
            if ($score < $this->minScore) {
                $score = $this->minScore;
            }
            
            $this->profit = $score*$this->profitMultiplier;
            $this->totalProfit += $this->profit;
            
            $this->user->earn($this->profit);
            
            
            // Cost
            $this->cost = $this->expectedCost;
            $this->totalCost += $this->cost;
            $this->expectedCost = $score*$this->costMultiplier;
            
            $this->user->pay($this->cost);
            
            
            $this->lastScore = $currentScore;
            $this->lastConnectionAt = $this->refreshedAt;
            $this->initRefreshedAt();
        }
    }

    /**
     * Set profit
     *
     * @param string $profit
     * @return UserRestaurant
     */
    public function setProfit($profit)
    {
        $this->profit = $profit;

        return $this;
    }

    /**
     * Get profit
     *
     * @return string 
     */
    public function getProfit()
    {
        $this->setProfitAndCost();
        
        return $this->profit;
    }

    /**
     * Set totalProfit
     *
     * @param string $totalProfit
     * @return UserRestaurant
     */
    public function setTotalProfit($totalProfit)
    {
        $this->totalProfit = $totalProfit;

        return $this;
    }

    /**
     * Get totalProfit
     *
     * @return string 
     */
    public function getTotalProfit()
    {
        return $this->totalProfit;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return UserRestaurant
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
     * @return UserRestaurant
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
     * Set lastConnectionAt
     *
     * @param \DateTime $lastConnectionAt
     * @return UserRestaurant
     */
    public function setLastConnectionAt($lastConnectionAt)
    {
        $this->lastConnectionAt = $lastConnectionAt;

        return $this;
    }

    /**
     * Get lastConnectionAt
     *
     * @return \DateTime 
     */
    public function getLastConnectionAt()
    {
        return $this->lastConnectionAt;
    }

    /**
     * Set cost
     *
     * @param string $cost
     * @return UserRestaurant
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return string 
     */
    public function getCost()
    {
        $this->setProfitAndCost();
        
        return $this->cost;
    }

    /**
     * Set expectedCost
     *
     * @param string $expectedCost
     * @return UserRestaurant
     */
    public function setExpectedCost($expectedCost)
    {
        $this->expectedCost = $expectedCost;

        return $this;
    }

    /**
     * Get expectedCost
     *
     * @return string 
     */
    public function getExpectedCost()
    {
        return $this->expectedCost;
    }

    /**
     * Set totalCost
     *
     * @param string $totalCost
     * @return UserRestaurant
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;

        return $this;
    }

    /**
     * Get totalCost
     *
     * @return string 
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }
}
