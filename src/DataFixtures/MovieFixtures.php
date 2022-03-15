<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Movie;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class MovieFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $categories = $manager->getRepository(Category::class)->findAll();

        for ($i = 1; $i <= 30; $i++) {
            $movie = new Movie();
            $movie
                ->setTitle('Movie : ' . $i)
                ->setDescription('Movie description : ' . $i)
                ->setReleasedAt(date('Y-m-d H:i:s'))
                ->setNote(rand(0,1) ? rand(0,5) : null);

            for ($j = 1; $j <= 3; $j++) {
                if (rand(0,1)) {
                    $movie->addCategory($categories[rand(0,14)]);
                }
            }
            $manager->persist($movie);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 2;
    }
}
