<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use Doctrine\ORM\EntityManagerInterface;
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
            'following' => false //TODO fix this
        ]);
    }

    #[Route('/editor', name: 'app_editor_new', methods: ['GET', 'POST'])]
    public function editorNew(Request $request, ArticleService $articleService): Response
    {
        return $this->manageFormAndCreateResponse(new Article(), $request, $articleService, 'app_editor_new');
    }

    public function manageFormAndCreateResponse(Article $article, Request $request, ArticleService $articleService, string $hxTarget): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $articleService->createOrUpdateArticle($article);

                return $this->redirectToRoute('app_article', [
                    'id' => $article->getId(),
                    'slug' => $article->getSlug(),
                ]);
            }
        }

        $isHTMXRequest = $request->headers->get('HX-Request', false);
        if ($isHTMXRequest) {
            return $this->render('article/components/editor-form-partial.html.twig', ['form' => $form, 'hxTarget' => $hxTarget]);
        }

        return $this->render('article/editor.html.twig', ['form' => $form, 'hxTarget' => $hxTarget]);
    }

    #[Route('/editor/{id:article}-{slug}', name: 'app_editor_edit', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-_]+'], methods: ['POST', 'GET'])]
    public function editArticle(Article $article, Request $request, ArticleService $articleService): Response
    {
        return $this->manageFormAndCreateResponse($article, $request, $articleService, 'app_editor_edit');
    }

    // GET is allowed to keep things working without JS (so no HX-DELETE)
    #[Route('/article/{id}/delete', name: 'app_article_delete', methods: ['GET', 'DELETE'])]
    public function delete(Article $article, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }
}
