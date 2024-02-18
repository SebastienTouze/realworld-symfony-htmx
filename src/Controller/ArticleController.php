<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    public function __construct(private readonly ArticleRepository $articleRepository) { }

    #[Route('/article/{slug}', name: 'app_article')]
    public function index(string $slug): Response
    {
        $article = $this->articleRepository->findOneBy(['slug' => $slug]);

        return $this->render('article/index.html.twig', [
            'article' => $article,
        ]);
    }
}
