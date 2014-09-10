<?php

namespace Tycoon\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Tycoon\ApiBundle\Entity\Postcode;
use Tycoon\ApiBundle\Entity\Restaurant;

class RestaurantController extends Controller {
    
    /**
     * @Route("/restaurants/list/")
     * 
     * Restaurants list
     */
    public function listxAction() {
        $response = new JsonResponse();
        
        // Get POST
        $datas = file_get_contents('php://input');
	$requestDatas = json_decode($datas);
        /**
        echo 'REMOVE THIS TEST'."\n";
        $requestDatas = array(
            'postcode' => 'EC2A',
            'userFacebookID' => '100001103256836'
        );
        $requestDatas = (object)$requestDatas;
        /**/
        
        if (!empty($requestDatas->postcode)) {
            $manager = $this->getDoctrine()->getManager();
            $postcodeRepo = $manager->getRepository('TycoonApiBundle:Postcode');

            // Load postcode
            $currentPostcode = $postcodeRepo->findOneByPostcode($requestDatas->postcode);

            if (empty($currentPostcode)) {
            // New postcode
                $currentPostcode = new Postcode();
                $currentPostcode->setPostcode($requestDatas->postcode);
            }

            if (empty($currentPostcode->getRefreshedAt()) || $currentPostcode->getRefreshedAt()->format('Y-m-d H:i:s') < date('Y-m-d H:i:s', time()-30*24*60*60)) {
            // Last refreshed more than 30 days ago: call JustEat API to refresh datas
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
                    $currentRestaurant->setRating($JERestaurant->RatingStars);
                    if (!empty($JERestaurant->NumberOfRatings)) {
                        $currentRestaurant->setNbRating($JERestaurant->NumberOfRatings);
                    }

                    $currentRestaurant->addPostcode($currentPostcode);
                    $currentPostcode->addRestaurant($currentRestaurant);

                    $currentRestaurant->initRefreshedAt();

                    $manager->persist($currentRestaurant);
                }
            }

            $restaurantsList = array();
            foreach($currentPostcode->getRestaurants() as $restaurant) {
                $restaurantsList[] = array(
                    'restaurantID' => $restaurant->getId(),
                    'name' => $restaurant->getName(),
                    'logo' => $restaurant->getLogo(),
                    'latitude' => $restaurant->getLatitude(),
                    'longitude' => $restaurant->getLongitude()
                );
            }

            $currentPostcode->initRefreshedAt();

            $manager->persist($currentPostcode);
            $manager->flush();

            $response->setData(array('restaurants' => $restaurantsList));
        } else {
            $response->setData(array('error' => 'Please send your postcode.'));
        }
        return $response;
    }
    
    
    private function _callJusteat($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://api-interview.just-eat.com/'.$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Basic VGVjaFRlc3RBUEk6dXNlcjI=",
                "Accept-Tenant: uk",
                "Accept-Language: en-GB",
                "Accept-Version: 2"
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $result = curl_exec($ch);

        curl_close($ch);
        
        return $result;
    }
}
