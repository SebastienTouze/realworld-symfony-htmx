<?php

namespace App\Service;

use App\Entity\Follow;
use App\Entity\User;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;

class FollowService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FollowRepository $followRepository,
    ) {
    }

    public function addUserToFollowed(User $follower, User $followed): void
    {
        $existingFollow = $this->followRepository->findOneBy(['follower' => $follower, 'followed' => $followed]);

        if (null !== $existingFollow) {
            return;
        }

        $followRelation = new Follow();
        $followRelation->setFollower($follower);
        $followRelation->setFollowed($followed);

        $this->entityManager->persist($followRelation);
        $this->entityManager->flush();
    }

    public function removeUserFromFollowed(User $follower, User $followed): void
    {
        $existingFollow = $this->followRepository->findOneBy(['follower' => $follower, 'followed' => $followed]);

        if (null !== $existingFollow) {
            $this->entityManager->remove($existingFollow);
            $this->entityManager->flush();
        }
    }

    public function isFollowing(User $follower, User $followed): bool
    {
        $existingFollow = $this->followRepository->findOneBy(['follower' => $follower, 'followed' => $followed]);

        return null !== $existingFollow;
    }
}
