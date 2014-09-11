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
            'postcode' => 'EC2A3JS',
            'userFacebookID' => '830297450337922'
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
                
                $restaurantsList[] = array(
                    'restaurantID' => $restaurant->getId(),
                    'name' => $restaurant->getName(),
                    'logo' => $restaurant->getLogo(),
                    'latitude' => $restaurant->getLatitude(),
                    'longitude' => $restaurant->getLongitude(),
                    'price' => $restaurant->getPrice(),
                    'isOwner' => $currentUser->ownRestaurant($restaurant),
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
}
