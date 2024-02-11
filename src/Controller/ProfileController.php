<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile/{username}', name: 'app_profile')]
    public function index(): Response
    {
        $article1 = (new Article())
            ->setTitle("How to build webapps that scale")
            ->setBody("No body (lol)")
            ->setSlug("how-to-build-webapps-that-scale")
            ->setDescription("This is the description for the post.")
            ->setCreatedAt(new \DateTimeImmutable("2024-01-20"))
            //no tags for now
        ;
        $article2 = (new Article())
            ->setTitle("The song you won't ever stop singing. No matter how hard you try.")
            ->setBody("No body (lol)")
            ->setSlug("the-song-you")
            ->setDescription("This is the description for the post.")
            ->setCreatedAt(new \DateTimeImmutable("2023-12-20"))
            //no tags for now
        ;

        $user = (new User())
            ->setBio("Cofounder @GoThinkster, lived in Aol's HQ for a few months, kinda looks like Peeta from the Hunger Games")
            ->setImage("http://i.imgur.com/Qr71crq.jpg")
            ->setUsername("eric-simons")
            ->addArticle($article1)
            ->addArticle($article2)
        ;

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}
