<?php

namespace App\Service;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ArticleService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[CurrentUser]
        private readonly Security $security,
    ) {
    }

    public function createOrUpdateArticle(Article $article): void
    {
        $this->generateSlug($article);
        $article->setCreatedAt(new \DateTimeImmutable());
        $article->setAuthor($this->security->getUser());

        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    /**
     * @throws UnprocessableEntityHttpException
     */
    private function generateSlug(Article $article): void
    {
        $title = $article->getTitle();

        if (null === $title || '' === $title) {
            throw new UnprocessableEntityHttpException('Article title can\'t be empty');
        }

        // Convert to lowercase
        $slug = mb_strtolower($title);

        // Replace characters with diacritical marks
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);

        // Replace non-alphanumeric characters with dashes
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remove leading/trailing dashes
        $slug = trim($slug, '-');

        // Limit to 250 characters
        if (strlen($slug) > 250) {
            $slug = substr($slug, 0, 250);
            $slug = rtrim($slug, '-');
        }

        $article->setSlug($slug);
    }
}
