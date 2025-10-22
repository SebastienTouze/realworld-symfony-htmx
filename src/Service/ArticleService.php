<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
        $article->setSlug($this->generateSlug($article));
        $user = $this->getConnectedUser();
        $article->setAuthor($user);

        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    public function addComment(Comment $comment, Article $article): void
    {
        $user = $this->getConnectedUser();
        $comment->setAuthor($user)
            ->setArticle($article)
            ->setCreatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }

    /**
     * @return non-empty-string
     *
     * @throws UnprocessableEntityHttpException
     */
    private function generateSlug(Article $article): string
    {
        $title = $article->getTitle();

        if (null === $title || '' === $title) {
            throw new UnprocessableEntityHttpException('Article title can\'t be empty');
        }

        // Convert to lowercase
        $slug = mb_strtolower($title);

        // Replace characters with diacritical marks
        $convertedSlug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
        if (false === $convertedSlug) {
            throw new UnprocessableEntityHttpException('Failed to convert slug encoding');
        }

        // Replace non-alphanumeric characters with dashes
        $slug = preg_replace('/[^a-z0-9]+/', '-', $convertedSlug);
        if (null === $slug) {
            throw new UnprocessableEntityHttpException('Failed to generate slug');
        }

        // Remove leading/trailing dashes
        $slug = trim($slug, '-');

        // Limit to 250 characters
        if (strlen($slug) > 250) {
            $slug = substr($slug, 0, 250);
            $slug = rtrim($slug, '-');
        }

        if ('' === $slug) {
            throw new UnprocessableEntityHttpException('Article title can\'t be empty');
        }

        return $slug;
    }

    private function getConnectedUser(): User
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if (null === $user) {
            throw new AccessDeniedException('User needs to be logged in to add a comment');
        }

        return $user;
    }
}
