<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class CategoryFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Action',
            'Adventure',
            'Comedy',
            'Crime',
            'Mystery',
            'Fantasy',
            'Historical',
            'Horror',
            'Romance',
            'Satire',
            'Science fiction',
            'Speculative',
            'Thriller',
            'Western',
            'Other'
        ];

        for ($i = 0; $i <= 14; $i++) {
            $category = new Category();
            $category->setTitle($categories[$i]);

            $manager->persist($category);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}
