<?php

namespace Tycoon\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RankCommand extends ContainerAwareCommand {
    
    protected function configure() {
        $this->setName('cron:rank')
                ->setDescription('Update users ranking');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $manager = $this->getContainer()->get('doctrine')->getEntityManager();

        // Get users
        $userRepo = $manager->getRepository('TycoonApiBundle:User');
        $users = $userRepo->findAll();
        
        // Sort users
        $rankedUsers = array();
        foreach($users as $user) {
            $value = $user->getValue();
            
            $rankedUsers[str_pad(round($value), 10, 0, STR_PAD_LEFT).'_'.str_pad($user->getId(), 5, 0, STR_PAD_LEFT)] = $user;
        }
        
        krsort($rankedUsers);
        
        $rank = 1;
        foreach($rankedUsers as $rankedUser) {
            $rankedUser->setRank($rank);
            $rank++;
            $manager->persist($rankedUser);
        }
        
        $manager->flush();
    }
    
    
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
}