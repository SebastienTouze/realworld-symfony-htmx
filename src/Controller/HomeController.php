<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $author = (new User())
            ->setBio("Cofounder @GoThinkster, lived in Aol's HQ for a few months, kinda looks like Peeta from the Hunger Games")
            ->setImage("http://i.imgur.com/Qr71crq.jpg")
            ->setUsername("eric-simons")
            ;

        $article1 = (new Article())
            ->setTitle("How to build webapps that scale")
            ->setBody("No body (lol)")
            ->setSlug("how-to-build-webapps-that-scale")
            ->setDescription("This is the description for the post.")
            ->setCreatedAt(new \DateTimeImmutable("2024-01-20"))
            ->setAuthor($author)
            //no tags for now
        ;
        $article2 = (new Article())
            ->setTitle("The song you won't ever stop singing. No matter how hard you try.")
            ->setBody("No body (lol)")
            ->setSlug("the-song-you")
            ->setDescription("This is the description for the post.")
            ->setCreatedAt(new \DateTimeImmutable("2023-12-20"))
            ->setAuthor($author)
            //no tags for now
        ;

        $someArticles = [$article1, $article2];


        return $this->render('home/index.html.twig', [
            'articles' => $someArticles,
        ]);
    }
}
