<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppInitialDemoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $article1 = (new Article())
            ->setTitle('How to build webapps that scale')
            ->setBody("<p>
          Web development technologies have evolved at an incredible clip over the past few years.
        </p>
        <h2>Introducing RealWorld.</h2>
        <p>It's a great solution for learning how other frameworks work.</p>")
            ->setSlug('how-to-build-webapps-that-scale')
            ->setDescription('This is the description for the post.')
            ->setCreatedAt(new \DateTimeImmutable('2024-01-20'))
            ->addTag($this->getReference('tag0', Tag::class))
            ->addTag($this->getReference('tag1', Tag::class))
        ;
        $article2 = (new Article())
            ->setTitle("The song you won't ever stop singing. No matter how hard you try.")
            ->setBody('Sing sing !')
            ->setSlug('the-song-you')
            ->setDescription('This is the description for the post.')
            ->setCreatedAt(new \DateTimeImmutable('2023-12-20'))
            ->addTag($this->getReference('tag0', Tag::class))
        ;
        $article3 = (new Article())
            ->setTitle('Top 3 advises for writing articles, 3rd will blow your mind!')
            ->setBody("1) don\'t loose time reading article, write them!")
            ->setSlug('top-3-advises-write-articles')
            ->setDescription('Incredible article!')
            ->setCreatedAt(new \DateTimeImmutable('2024-09-26'))
            ->addTag($this->getReference('tag1', Tag::class))
        ;

        $user = (new User())
            ->setBio("Cofounder @GoThinkster, lived in Aol's HQ for a few months, kinda looks like Peeta from the Hunger Games")
            ->setImage('http://i.imgur.com/Qr71crq.jpg')
            ->setUsername('eric-simons')
            ->setEmail('some@mail.com')
            ->setPassword('$2y$13$T9nNU0B3xDmUR5BqCPkZfeZHNJ69WYp/oXU4UYb2gPBi7HCH6ley.') // 1234
            ->addArticle($article1)
            ->addArticle($article2)
            ->addArticle($article3)
        ;

        $userVisitor = (new User())
            ->setBio('')
            ->setImage('')
            ->setUsername('nemo')
            ->setEmail('nemo@email.com')
            ->setPassword('$2y$13$drQ.NoVIhLEYI0RzT6yQWeDWORjBrpwbWXBiCci87ZMQABpSgPVai') // 123456
        ;

        $manager->persist($user);
        $manager->persist($userVisitor);
        $manager->persist($article1);
        $manager->persist($article2);
        $manager->persist($article3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [TagFixture::class];
    }
}
