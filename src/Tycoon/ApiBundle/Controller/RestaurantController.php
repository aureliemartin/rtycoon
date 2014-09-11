<?php

namespace Tycoon\ApiBundle\Controller;

use Tycoon\ApiBundle\Controller\ApiController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Tycoon\ApiBundle\Entity\Postcode;
use Tycoon\ApiBundle\Entity\User;

class RestaurantController extends ApiController {
    
    /**
     * @Route("/restaurants/list/")
     * 
     * Restaurants list
     */
    public function listAction() {
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
        
        if (!empty($requestDatas->postcode) && !empty($requestDatas->userFacebookID)) {
            $manager = $this->getDoctrine()->getManager();
            
            // Get current user
            $userRepo = $manager->getRepository('TycoonApiBundle:User');
            $currentUser = $userRepo->findOneByFacebookId($requestDatas->userFacebookID);
            if (empty($currentUser)) {
                $currentUser = new User();
                $currentUser->setFacebookId($requestDatas->userFacebookID);
                $manager->persist($currentUser);
            }
            

            // Load postcode
            $postcodeRepo = $manager->getRepository('TycoonApiBundle:Postcode');
            $currentPostcode = $postcodeRepo->findOneByPostcode($requestDatas->postcode);

            if (empty($currentPostcode)) {
            // New postcode
                $currentPostcode = new Postcode();
                $currentPostcode->setPostcode($requestDatas->postcode);
            }

            if (empty($currentPostcode->getRefreshedAt()) || $currentPostcode->getRefreshedAt()->format('Y-m-d') < date('Y-m-d', time()-$currentPostcode->getRefreshingTime())) {
            // Last refreshed more than 30 days ago: call JustEat API to refresh datas
                $currentPostcode = $this->_refreshPostcode($currentPostcode);
            }
            
            $restaurantsList = array();
            foreach($currentPostcode->getRestaurants() as $restaurant) {
                
                if (empty($currentPostcode->getRefreshedAt()) || $currentPostcode->getRefreshedAt()->format('Y-m-d') < date('Y-m-d', time()-$restaurant->getRefreshingTime())) {
                // Last refreshed more than 1 day ago: call JustEat API to refresh datas
                    $this->_refreshPostcode($currentPostcode);
                    $manager->refresh($restaurant);
                }
                
                $cuisines = array();
                foreach($restaurant->getCuisines() as $cuisine) {
                    $cuisines[] = array(
                        'cuisineID' => $cuisine->getId(),
                        'name' => $cuisine->getName()
                    );
                }
                
                $isOwner = $currentUser->ownRestaurant($restaurant);
                
                $restaurantsList[] = array(
                    'restaurantID' => $restaurant->getId(),
                    'name' => $restaurant->getName(),
                    'address' => $restaurant->getAddress().' '.$restaurant->getCity(),
                    'url' => $restaurant->getUrl(),
                    'logo' => $restaurant->getLogo(),
                    'latitude' => $restaurant->getLatitude(),
                    'longitude' => $restaurant->getLongitude(),
                    'price' => $restaurant->getPrice(),
                    'isOwner' => $isOwner,
                    'cuisines' => $cuisines
                );
                
                $manager->persist($restaurant);
            }
            
            $manager->flush();
        
            /**
            echo '<pre>';
            print_r($restaurantsList);
            die();
            /**/

            $response->setData(array('restaurants' => $restaurantsList));
        } else {
            $response->setData(array('error' => 'Please send your postcode.'));
        }
        
        return $response;
    }
    
    /**
     * @Route("/restaurants/details/")
     * 
     * Restaurant's details
     */
    public function detailsAction() {
        $response = new JsonResponse();
        
        // Get POST
        $datas = file_get_contents('php://input');
	$requestDatas = json_decode($datas);
        
        if (!empty($requestDatas->userFacebookID) && !empty($requestDatas->restaurantID)) {
            $manager = $this->getDoctrine()->getManager();
            
            // Get current user
            $userRepo = $manager->getRepository('TycoonApiBundle:User');
            $currentUser = $userRepo->findOneByFacebookId($requestDatas->userFacebookID);
            if (empty($currentUser)) {
                $currentUser = new User();
                $currentUser->setFacebookId($requestDatas->userFacebookID);
                $manager->persist($currentUser);
            }
            

            // Load restaurant
            $restaurantRepo = $manager->getRepository('TycoonApiBundle:Restaurant');
            $currentRestaurant = $restaurantRepo->find($requestDatas->restaurantID);

            if (!empty($currentRestaurant)) {
                // Refresh restaurant
                $currentPostcode = null;
                foreach($currentRestaurant->getPostcodes() as $postcode) {
                    $currentPostcode = $postcode;
                    break;
                }

                if (empty($currentPostcode->getRefreshedAt()) || $currentPostcode->getRefreshedAt()->format('Y-m-d') < date('Y-m-d', time()-$currentRestaurant->getRefreshingTime())) {
                // Last refreshed more than 30 days ago: call JustEat API to refresh datas
                    $currentPostcode = $this->_refreshPostcode($currentPostcode);
                    $manager->refresh($currentRestaurant);
                }

                $cuisines = array();
                foreach($currentRestaurant->getCuisines() as $cuisine) {
                    $cuisines[] = array(
                        'cuisineID' => $cuisine->getId(),
                        'name' => $cuisine->getName()
                    );
                }
                
                $isOwner = $currentUser->ownRestaurant($currentRestaurant);

                $restaurant = array(
                    'restaurantID' => $currentRestaurant->getId(),
                    'name' => $currentRestaurant->getName(),
                    'address' => $currentRestaurant->getAddress().' '.$currentRestaurant->getCity(),
                    'logo' => $currentRestaurant->getLogo(),
                    'url' => $currentRestaurant->getUrl(),
                    'latitude' => $currentRestaurant->getLatitude(),
                    'longitude' => $currentRestaurant->getLongitude(),
                    'price' => $currentRestaurant->getPrice(),
                    'isOwner' => $isOwner,
                    'cuisines' => $cuisines
                );
                
                if ($isOwner) {
                    // Load userRestaurant
                    $userRestaurantRepo = $manager->getRepository('TycoonApiBundle:UserRestaurant');
                    $userRestaurant = $userRestaurantRepo->getByUserAndRestaurant($currentUser, $currentRestaurant);
                    
                    $restaurant['initialPrice'] = $userRestaurant->getInitialPrice();
                    $restaurant['lastLogin'] = $userRestaurant->getLastConnectionAt()->format('Y-m-d');
                    $restaurant['profitSinceLastLogin'] = $userRestaurant->getProfit();
                    $restaurant['costSinceLastLogin'] = $userRestaurant->getCost();
                    
                    $manager->persist($userRestaurant);
                    $manager->persist($currentUser);
                } else {
                    $restaurant['estimatedProfit'] = $currentRestaurant->getEstimatedProfit();
                    $restaurant['estimatedCost'] = $currentRestaurant->getEstimatedCost();
                }

                $manager->persist($currentRestaurant);

                $manager->flush();

                $response->setData(array('restaurant' => $restaurant));
            } else {
                $response->setData(array('error' => 'This restaurant does not exist.'));
            }
        } else {
            $response->setData(array('error' => 'Please send your Facebook ID.'));
        }
        
        return $response;
    }
}
