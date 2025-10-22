<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private UserService $userService;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        // Create service instance
        $this->userService = new UserService($this->passwordHasher, $this->entityManager);
    }

    public function testSaveUserHashesPasswordAndRemovePlainTextPassword(): void
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        $user->setPlainPassword('secret123');

        $hashedPassword = '$2y$13$hashedpassword';
        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'secret123')
            ->willReturn($hashedPassword);

        // Expect persist and flush
        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->userService->saveUser($user);

        $this->assertEquals($hashedPassword, $user->getPassword());
        $this->assertNull($user->getPlainPassword());
    }

    public function testSaveUserDoesNotHashWhenNoPlainPassword(): void
    {
        // User with hashed password = existing user
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        $existingHashedPassword = '$2y$13$existinghashedpassword';
        $user->setPassword($existingHashedPassword);

        // Password hasher should not be called
        $this->passwordHasher
            ->expects($this->never())
            ->method('hashPassword');

        // Expect persist and flush
        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->userService->saveUser($user);

        $this->assertEquals($existingHashedPassword, $user->getPassword());
    }
}
