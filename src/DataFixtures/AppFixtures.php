<?php

namespace App\DataFixtures;

use App\Factory\CategoryFactory;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {

        CategoryFactory::createMany(10);
        UserFactory::createMany(100);
        ProductFactory::createMany(50, function() {
            return [
                'category' => CategoryFactory::random(),
            ];
        });
    }
}
