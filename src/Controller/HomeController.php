<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    const int ELEMENT_PER_PAGE = 2;

    public function __construct(private readonly ArticleRepository $articleRepository) { }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $page = (int)($request->query->get('page', 1));
        $page <= 1 && $page = 1;

        $paginatedArticles = $this->articleRepository->findAllPaginated($page, self::ELEMENT_PER_PAGE);

        return $this->render('home/index.html.twig', [
            'paginatedArticles' => $paginatedArticles,
            'currentPage' => $page,
            'lastPage' => ceil(count($paginatedArticles) / self::ELEMENT_PER_PAGE),
        ]);
    }
}
