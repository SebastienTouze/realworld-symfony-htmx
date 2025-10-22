<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        #[Autowire(env: 'ELEMENT_PER_PAGE')]
        private readonly int $elementPerPage,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $page = (int) $request->query->get('page', '1');
        if ($page <= 1) {
            $page = 1;
        }

        $paginatedArticles = $this->articleRepository->findAllPaginated($page, $this->elementPerPage);

        return $this->render('home/index.html.twig', [
            'activeFeed' => 'global',
            'paginatedArticles' => $paginatedArticles,
            'currentPage' => $page,
            'lastPage' => ceil(count($paginatedArticles) / $this->elementPerPage),
        ]);
    }

    #[Route('/tag-feed/{label:tag}', name: 'app_home_tag_feed')]
    public function tagFeed(Tag $tag, Request $request): Response
    {
        $page = (int) $request->query->get('page', '1');
        if ($page <= 1) {
            $page = 1;
        }

        $paginatedArticles = $this->articleRepository->findByTagPaginated($tag, $page, $this->elementPerPage);

        return $this->render('home/tag-feed.html.twig', [
            'activeFeed' => $tag->getLabel(),
            'paginatedArticles' => $paginatedArticles,
            'currentPage' => $page,
            'lastPage' => ceil(count($paginatedArticles) / $this->elementPerPage),
        ]);
    }

    #[Route('/your-feed', name: 'app_home_your_feed')]
    public function yourFeed(Request $request): Response
    {
        $page = (int) $request->query->get('page', '1');
        if ($page <= 1) {
            $page = 1;
        }

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        $paginatedArticles = $this->articleRepository->findByFavoritedAuthorPaginated($user, $page, $this->elementPerPage);

        return $this->render('home/index.html.twig', [
            'activeFeed' => 'your',
            'paginatedArticles' => $paginatedArticles,
            'currentPage' => $page,
            'lastPage' => ceil(count($paginatedArticles) / $this->elementPerPage),
        ]);
    }
}
