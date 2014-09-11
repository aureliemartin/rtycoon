<?php

namespace Tycoon\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Tycoon\ApiBundle\Entity\Restaurant;
use Tycoon\ApiBundle\Entity\Cuisine;

class ApiController extends Controller {
    
    
    /**
     * Refresh restaurants by postcode
     * 
     * @param Postcode $currentPostcode
     * @return Postcode
     */
    protected function _refreshPostcode($currentPostcode) {
        $manager = $this->getDoctrine()->getManager();
        
        $result = $this->_callJusteat('restaurants?q='.$currentPostcode->getPostcode());

        $JERestaurants = json_decode($result);

        $restaurantRepo = $manager->getRepository('TycoonApiBundle:Restaurant');
        $cuisineRepo = $manager->getRepository('TycoonApiBundle:Cuisine');
        foreach($JERestaurants->Restaurants as $JERestaurant) { 
            // Load restaurant
            $currentRestaurant = $restaurantRepo->findOneByJusteatId($JERestaurant->Id);

            if (empty($currentRestaurant)) {
            // Create restaurant
                $currentRestaurant = new Restaurant();
                $currentRestaurant->setJusteatId($JERestaurant->Id);
            }

            $currentRestaurant->setName($JERestaurant->Name);
            $currentRestaurant->setAddress($JERestaurant->Address);
            if (!empty($JERestaurant->City)) {
                $currentRestaurant->setCity($JERestaurant->City);
            }
            if (!empty($JERestaurant->Url)) {
                $currentRestaurant->setUrl($JERestaurant->Url);
            }
            if (!empty($JERestaurant->Logo[0]->StandardResolutionURL)) {
                $currentRestaurant->setLogo($JERestaurant->Logo[0]->StandardResolutionURL);
            }

            if (!empty($JERestaurant->Latitude)) {
                $currentRestaurant->setLatitude($JERestaurant->Latitude);
            }
            if (!empty($JERestaurant->Longitude)) {
                $currentRestaurant->setLongitude($JERestaurant->Longitude);
            }
            if (!empty($JERestaurant->Score)) {
                $currentRestaurant->setScore($JERestaurant->Score);
            }
            
            foreach($JERestaurant->CuisineTypes as $cuisineType) {
                // Load cuisine
                $currentCuisine = $cuisineRepo->findOneByJusteatId($cuisineType->Id);

                if (empty($currentCuisine)) {
                // Create cuisine
                    $currentCuisine = new Cuisine();
                    $currentCuisine->setJusteatId($cuisineType->Id);
                    $currentCuisine->setName($cuisineType->Name);
                }
                
                $currentRestaurant->addCuisine($currentCuisine);
                $currentCuisine->addRestaurant($currentRestaurant);
                
                $manager->persist($currentCuisine);
            }

            $currentRestaurant->addPostcode($currentPostcode);
            $currentPostcode->addRestaurant($currentRestaurant);

            $manager->persist($currentRestaurant);
            $manager->flush();
        }
        
        $currentPostcode->initRefreshedAt();
        
        $manager->persist($currentPostcode);
        $manager->flush();
        
        return $currentPostcode;
    }
    
    
    /**
     * Call just eat API
     * 
     * @param $url string
     * 
     * @return $result string
     */
    protected function _callJusteat($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://api-interview.just-eat.com/'.$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Basic VGVjaFRlc3RBUEk6dXNlcjI=",
                "Accept-Tenant: uk",
                "Accept-Language: en-GB",
                "Accept-Version: 2",
                "User-Agent: RestaurantTycoon"
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $result = curl_exec($ch);

        curl_close($ch);
        
        return $result;
    }
    
    
    /**
     * Get restaurant discounted price for a user
     * 
     * @param User $currentUser
     * @param Restaurant $restaurant
     * @return float
     */
    protected function _getDiscountedPrice($currentUser, $restaurant) {
        $manager = $this->getDoctrine()->getManager();
            
        $restaurantRepo = $manager->getRepository('TycoonApiBundle:Restaurant');
        
        $restaurantPrice = $restaurant->getPrice();
        $discountedPrice = $restaurantPrice;

        // User already ordered on Just Eat?
        if (!empty($currentUser->getJusteatEmail())) {
            if (empty($currentUser->getRefreshedAt()) || $currentUser->getRefreshedAt()->format('Y-m-d') < date('Y-m-d', time()-$currentUser->getRefreshingTime())) {
                // Last refreshed more than 1 day ago: call JustEat API to refresh datas
                $result = $this->_callJusteat('restaurant-ids/'.$currentUser->getJusteatEmail());

                $JERestaurantIds = json_decode($result);

                foreach($JERestaurantIds->RestaurantIds as $JERestaurantId) {
                    // Load restaurant
                    $orderRestaurant = $restaurantRepo->findOneByJusteatId($JERestaurantId);
                    if (!empty($orderRestaurant)) {
                        $currentUser->addOrderRestaurant($orderRestaurant);
                    }
                }

                $currentUser->initRefreshedAt();

                $manager->persist($currentUser);
                $manager->flush();
            }

            if ($currentUser->orderedInRestaurant($restaurant)) {
                $discountedPrice = $restaurantPrice - ($restaurantPrice*0.05);
            }
        }
        
        return $discountedPrice;
    }
}
