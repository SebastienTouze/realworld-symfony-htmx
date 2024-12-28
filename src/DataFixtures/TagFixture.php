<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tag0 = new Tag('programming');
        $tag1 = new Tag('javascript');
        $tag2 = new Tag('emberjs');
        $tag3 = new Tag('angularjs');
        $tag4 = new Tag('react');
        $tag5 = new Tag('mean');
        $tag6 = new Tag('node');
        $tag7 = new Tag('rails');

         $manager->persist($tag0);
         $manager->persist($tag1);
         $manager->persist($tag2);
         $manager->persist($tag3);
         $manager->persist($tag4);
         $manager->persist($tag5);
         $manager->persist($tag6);
         $manager->persist($tag7);

        $manager->flush();
    }
}
