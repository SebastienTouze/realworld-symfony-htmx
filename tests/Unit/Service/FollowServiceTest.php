<?php

namespace App\Tests\Unit\Service;

use App\Entity\Follow;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Service\FollowService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class FollowServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private FollowRepository $followRepository;
    private FollowService $followService;
    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->followRepository = $this->createMock(FollowRepository::class);

        // Create service instance
        $this->followService = new FollowService($this->entityManager, $this->followRepository);

        // Create test users
        $this->userA = new User();
        $this->userA->setUsername('userA');
        $this->userA->setEmail('userA@example.com');

        $this->userB = new User();
        $this->userB->setUsername('userB');
        $this->userB->setEmail('userB@example.com');
    }

    public function testAddUserToFollowed(): void
    {
        $follower = $this->userA;
        $followed = $this->userB;

        // No existing follow
        $this->followRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['follower' => $follower, 'followed' => $followed])
            ->willReturn(null);

        // Expects a new Follow entity to be persisted
        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->callback(function ($follow) use ($follower, $followed) {
                return $follow instanceof Follow
                    && $follow->getFollower() === $follower
                    && $follow->getFollowed() === $followed;
            }));
        $this->entityManager->expects($this->once())->method('flush');

        $this->followService->addUserToFollowed($follower, $followed);
    }

    public function testAddDuplicateFollowIsIdempotent(): void
    {
        $follower = $this->userA;
        $followed = $this->userB;
        $existingFollow = new Follow();
        $existingFollow->setFollower($follower)
            ->setFollowed($followed);

        // Existing follow found
        $this->followRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['follower' => $follower, 'followed' => $followed])
            ->willReturn($existingFollow);

        // No persisting in DB
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->followService->addUserToFollowed($follower, $followed);
    }

    public function testRemoveUserFromFollowed(): void
    {
        $follower = $this->userA;
        $followed = $this->userB;
        $follow = new Follow();
        $follow->setFollower($follower)
            ->setFollowed($followed);

        // Existing follow found
        $this->followRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['follower' => $follower, 'followed' => $followed])
            ->willReturn($follow);

        // Follow is removed
        $this->entityManager->expects($this->once())->method('remove')->with($follow);
        $this->entityManager->expects($this->once())->method('flush');

        $this->followService->removeUserFromFollowed($follower, $followed);
    }

    public function testRemoveNonExistentFollowDoesNothing(): void
    {
        $follower = $this->userA;
        $followed = $this->userB;

        // Mock repository to return null (no follow exists)
        $this->followRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['follower' => $follower, 'followed' => $followed])
            ->willReturn(null);

        // No removing from DB
        $this->entityManager->expects($this->never())->method('remove');
        $this->entityManager->expects($this->never())->method('flush');

        $this->followService->removeUserFromFollowed($follower, $followed);
    }

    public function testIsFollowingReturnsTrueWhenFollowing(): void
    {
        $follower = $this->userA;
        $followed = $this->userB;
        $follow = new Follow();
        $follow->setFollower($follower)
            ->setFollowed($followed);

        // Existing follow found
        $this->followRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['follower' => $follower, 'followed' => $followed])
            ->willReturn($follow);

        $result = $this->followService->isFollowing($follower, $followed);

        $this->assertTrue($result);
    }

    public function testIsFollowingReturnsFalseWhenNotFollowing(): void
    {
        $follower = $this->userA;
        $followed = $this->userB;

        // No follow relationship exists
        $this->followRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['follower' => $follower, 'followed' => $followed])
            ->willReturn(null);

        $result = $this->followService->isFollowing($follower, $followed);

        $this->assertFalse($result);
    }
}
