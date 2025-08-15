<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function saveUser(User $user): void
    {
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $user->getPlainPassword()
            )
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
