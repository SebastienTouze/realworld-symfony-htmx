<?php

namespace App\Controller;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        $article1 = (new Article())
        ->setTitle("How to build webapps that scale")
            ->setBody("No body (lol)")
            ->setSlug("how-to-build-webapps-that-scale")
            ->setDescription("This is the description for the post.")
            //no tags for now
        ;
        $article2 = (new Article())
        ->setTitle("The song you won't ever stop singing. No matter how hard you try.")
            ->setBody("No body (lol)")
            ->setSlug("the-song-you")
            ->setDescription("This is the description for the post.")
            //no tags for now
        ;

        $someArticles = [$article1, $article2];


        return $this->render('home/index.html.twig', [
            'articles' => $someArticles,
        ]);
    }
}
