<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppInitialDemoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $article1 = (new Article())
            ->setTitle("How to build webapps that scale")
            ->setBody("<p>
          Web development technologies have evolved at an incredible clip over the past few years.
        </p>
        <h2>Introducing RealWorld.</h2>
        <p>It's a great solution for learning how other frameworks work.</p>")
            ->setSlug("how-to-build-webapps-that-scale")
            ->setDescription("This is the description for the post.")
            ->setCreatedAt(new \DateTimeImmutable("2024-01-20"))
            //no tags for now
        ;
        $article2 = (new Article())
            ->setTitle("The song you won't ever stop singing. No matter how hard you try.")
            ->setBody("Sing sing !")
            ->setSlug("the-song-you")
            ->setDescription("This is the description for the post.")
            ->setCreatedAt(new \DateTimeImmutable("2023-12-20"))
            //no tags for now
        ;

        $user = (new User())
            ->setBio("Cofounder @GoThinkster, lived in Aol's HQ for a few months, kinda looks like Peeta from the Hunger Games")
            ->setImage("http://i.imgur.com/Qr71crq.jpg")
            ->setUsername("eric-simons")
            ->setEmail("some@mail.com")
            ->setPassword('$2y$13$T9nNU0B3xDmUR5BqCPkZfeZHNJ69WYp/oXU4UYb2gPBi7HCH6ley.') //1234
            ->addArticle($article1)
            ->addArticle($article2)
        ;

        $manager->persist($user);
        $manager->persist($article1);
        $manager->persist($article2);

        $manager->flush();
    }
}
