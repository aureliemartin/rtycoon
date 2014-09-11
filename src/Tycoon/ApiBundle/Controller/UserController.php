<?php

namespace Tycoon\ApiBundle\Controller;

use Tycoon\ApiBundle\Controller\ApiController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        /**/
        echo 'REMOVE THIS TEST'."\n";
        $requestDatas = array(
            'userFacebookID' => '100001103256836',
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
            $currentRestaurant = $restaurantRepo->findOneById($requestDatas->restaurantID);

            if (!empty($currentRestaurant)) {
                
                if (empty($currentPostcode->getRefreshedAt()) || $currentPostcode->getRefreshedAt()->format('Y-m-d') < date('Y-m-d', time()-$restaurant->getRefreshingTime())) {
                // Last refreshed more than 1 day ago: call JustEat API to refresh datas
                    $this->_refreshPostcode($currentPostcode);
                }
            } else {
                $response->setData(array('error' => 'This restaurant does not exist.'));
            }
        } else {
            $response->setData(array('error' => 'Please send your Facebook ID.'));
        }
        
        return $response;
    }
}
