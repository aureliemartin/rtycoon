<?php

namespace Tycoon\ApiBundle\Controller;

use Tycoon\ApiBundle\Controller\ApiController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Tycoon\ApiBundle\Entity\User;
use Tycoon\ApiBundle\Entity\UserRestaurant;

class UserController extends ApiController {
    
    /**
     * @Route("/user/buyrestaurant/")
     * 
     * Buy a restaurant
     */
    public function buyrestaurantAction() {
        $response = new JsonResponse();
        
        // Get POST
        $datas = file_get_contents('php://input');
	$requestDatas = json_decode($datas);
        /**
        echo 'REMOVE THIS TEST'."\n";
        $requestDatas = array(
            'userFacebookID' => '1000041103256836',
            'restaurantID' => 1
        );
        $requestDatas = (object)$requestDatas;
        /**/
        
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
                
                if (!$currentUser->ownRestaurant($currentRestaurant)) {
                    $currentPostcode = null;
                    foreach($currentRestaurant->getPostcodes() as $postcode) {
                        $currentPostcode = $postcode;
                        break;
                    }

                    if (empty($currentPostcode->getRefreshedAt()) || $currentPostcode->getRefreshedAt()->format('Y-m-d') < date('Y-m-d', time()-$currentRestaurant->getRefreshingTime())) {
                    // Last refreshed more than 1 day ago: call JustEat API to refresh datas
                        $this->_refreshPostcode($currentPostcode);
                        $manager->refresh($currentRestaurant);
                        $manager->persist($currentRestaurant);
                    }

                    $restaurantPrice = $currentRestaurant->getPrice();

                    if ($currentUser->getMoney() >= $restaurantPrice) {
                        $userRestaurant = new UserRestaurant();
                        $userRestaurant->setUser($currentUser);
                        $userRestaurant->setRestaurant($currentRestaurant);

                        $currentUser->addUserRestaurant($userRestaurant);
                        $currentRestaurant->addUserRestaurant($userRestaurant);

                        $currentUser->pay($restaurantPrice);

                        $manager->persist($userRestaurant);
                        $manager->persist($currentUser);
                        $manager->persist($currentRestaurant);

                        $response->setData(
                            array(
                                'success' => array(
                                    'user' => array(
                                        'userID' => $currentUser->getId(),
                                        'money' => $currentUser->getMoney()
                                    )
                                )
                            )
                        );
                    } else {
                        $response->setData(array('error' => "You don't have enough money to buy this restaurant."));
                    }
                } else {
                    $response->setData(array('error' => "You already own this restaurant."));
                }
                
                $manager->flush();
            } else {
                $response->setData(array('error' => 'This restaurant does not exist.'));
            }
        } else {
            $response->setData(array('error' => 'Please send your Facebook ID.'));
        }
        
        return $response;
    }
    
    /**
     * @Route("/user/sellrestaurant/")
     * 
     * Sell a restaurant
     */
    public function sellrestaurantAction() {
        $response = new JsonResponse();
        
        // Get POST
        $datas = file_get_contents('php://input');
	$requestDatas = json_decode($datas);
        /**
        echo 'REMOVE THIS TEST'."\n";
        $requestDatas = array(
            'userFacebookID' => '1000041103256836',
            'restaurantID' => 1
        );
        $requestDatas = (object)$requestDatas;
        /**/
        
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
                
                if ($currentUser->ownRestaurant($currentRestaurant)) {
                    $currentPostcode = null;
                    foreach($currentRestaurant->getPostcodes() as $postcode) {
                        $currentPostcode = $postcode;
                        break;
                    }

                    if (empty($currentPostcode->getRefreshedAt()) || $currentPostcode->getRefreshedAt()->format('Y-m-d') < date('Y-m-d', time()-$currentRestaurant->getRefreshingTime())) {
                    // Last refreshed more than 1 day ago: call JustEat API to refresh datas
                        $this->_refreshPostcode($currentPostcode);
                        $manager->refresh($currentRestaurant);
                        $manager->persist($currentRestaurant);
                    }

                    
                    $restaurantPrice = $currentRestaurant->getPrice();
                    
                    // Load userRestaurant
                    $userRestaurantRepo = $manager->getRepository('TycoonApiBundle:UserRestaurant');
                    $userRestaurant = $userRestaurantRepo->getByUserAndRestaurant($currentUser, $currentRestaurant);

                    $currentUser->earn($restaurantPrice);

                    $manager->remove($userRestaurant);
                    $manager->persist($currentUser);
                    $manager->persist($currentRestaurant);

                    $response->setData(
                        array(
                            'success' => array(
                                'user' => array(
                                    'userID' => $currentUser->getId(),
                                    'money' => $currentUser->getMoney()
                                )
                            )
                        )
                    );
                } else {
                    $response->setData(array('error' => "You don't own this restaurant."));
                }
                
                $manager->flush();
            } else {
                $response->setData(array('error' => 'This restaurant does not exist.'));
            }
        } else {
            $response->setData(array('error' => 'Please send your Facebook ID.'));
        }
        
        return $response;
    }
    
    /**
     * @Route("/user/restaurants/list/")
     * 
     * List of a user's restaurants
     */
    public function restaurantlistAction() {
        $response = new JsonResponse();
        
        // Get POST
        $datas = file_get_contents('php://input');
	$requestDatas = json_decode($datas);
        /**/
        echo 'REMOVE THIS TEST'."\n";
        $requestDatas = array(
            'userFacebookID' => '1000041103256836'
        );
        $requestDatas = (object)$requestDatas;
        /**/
        
        if (!empty($requestDatas->userFacebookID)) {
            $manager = $this->getDoctrine()->getManager();
            
            // Get current user
            $userRepo = $manager->getRepository('TycoonApiBundle:User');
            $currentUser = $userRepo->findOneByFacebookId($requestDatas->userFacebookID);
            if (empty($currentUser)) {
                $currentUser = new User();
                $currentUser->setFacebookId($requestDatas->userFacebookID);
                $manager->persist($currentUser);
            }
            

            // Load restaurants
            $userRestaurants = $currentUser->getUserRestaurants();
        } else {
            $response->setData(array('error' => 'Please send your Facebook ID.'));
        }
        
        return $response;
    }
}
