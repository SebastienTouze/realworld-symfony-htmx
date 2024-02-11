<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{

    public function __construct(private ArticleRepository $articleRepository) { }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $someArticles = $this->articleRepository->findAll();

        return $this->render('home/index.html.twig', [
            'articles' => $someArticles,
        ]);
    }
}
