<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    public function __construct(private readonly ArticleRepository $articleRepository)
    {
    }

    #[Route('/article/{id}-{slug}', name: 'app_article', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-_]+'])]
    public function index(string $slug): Response
    {
        $article = $this->articleRepository->findOneBy(['slug' => $slug]);

        return $this->render('article/index.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/editor', name: 'app_editor', methods: ['GET', 'POST'])]
    public function editor(Request $request, ArticleService $articleService): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $articleService->createOrUpdateArticle($article);
            }
        }

        $isHTMXRequest = $request->headers->get('HX-Request', false);
        if ($isHTMXRequest) {
            return $this->render('article/components/editor-form-partial.html.twig', ['form' => $form]);
        }

        return $this->render('article/editor.html.twig', ['form' => $form]);
    }
}
