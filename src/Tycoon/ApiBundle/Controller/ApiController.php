<?php

namespace Tycoon\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Tycoon\ApiBundle\Entity\Restaurant;

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
        foreach($JERestaurants->Restaurants as $JERestaurant) { 
            // Load restaurant
            $currentRestaurant = $restaurantRepo->findOneByJusteatId($JERestaurant->Id);

            if (empty($currentRestaurant)) {
            // Create restaurant
                $currentRestaurant = new Restaurant();
                $currentRestaurant->setJusteatId($JERestaurant->Id);
            }

            $currentRestaurant->setName($JERestaurant->Name);
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

            $currentRestaurant->addPostcode($currentPostcode);
            $currentPostcode->addRestaurant($currentRestaurant);

            $manager->persist($currentRestaurant);
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
}
