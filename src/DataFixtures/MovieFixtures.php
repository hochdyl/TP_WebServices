<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;

class MovieFixtures extends Fixture
{
    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $notes = [null, 0, 1, 2, 3, 4, 5];

        for ($i = 0; $i < 30; $i++) {
            $movie = new Movie();
            $movie
                ->setTitle('Movie : ' . $i)
                ->setDescription('Movie description : ' . $i)
                ->setReleasedAt(date('Y-m-d H:i:s'))
                ->setNote($notes[array_rand($notes)]);

            $manager->persist($movie);
        }
        $manager->flush();
    }
}
