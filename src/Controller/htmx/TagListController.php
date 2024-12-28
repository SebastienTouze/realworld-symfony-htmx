<?php

namespace App\Controller\htmx;

use App\Entity\Tag;
use App\Repository\ArticleRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('htmx/tags')]
class TagListController extends AbstractController
{
    public function __construct(
        private readonly TagRepository $tagRepository) { }

    #[Route('/', name: 'app_tag_list', methods: ['GET'])]
    public function list(): Response
    {
        $tags = $this->tagRepository->findAll();

        return $this->render('components/tag-list.html.twig', ['tags' => $tags]);
    }
}
