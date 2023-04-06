<?php

namespace App\DataFixtures;

use App\Factory\CampusFactory;
use App\Factory\ParticipantFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void{


//        CampusFactory::createMany(3);
        ParticipantFactory::createMany(10);
        // $product = new Product();
        // $manager->persist($product);
        $manager->flush();
    }
}
