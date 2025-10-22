<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentsController extends AbstractController
{
    #[Route('/article/{id:article}/comments', name: 'app_comments_show', methods: ['GET'])]
    public function showAll(Article $article): Response
    {
        return $this->render('article/components/comments.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/article/{id:article}/comments', name: 'app_comments_new', methods: ['POST'])]
    public function newComment(Article $article, ArticleService $articleService, Request $request): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class,
            $comment,
            ['action' => $this->generateUrl('app_comments_new', ['id' => $article->getId()])]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleService->addComment($comment, $article);
            // reset the form
            $form = $this->createForm(CommentType::class,
                new Comment(),
                ['action' => $this->generateUrl('app_comments_new', ['id' => $article->getId()])]
            );
        }

        return $this->render('article/components/comments.html.twig', [
            'commentForm' => $form,
            'article' => $article,
        ]);
    }
}
