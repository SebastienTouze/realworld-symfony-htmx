<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ApiResource]
class Tag
{
    public function __construct(
        #[ORM\Column(length: 255)]
        private string $label
    ) { }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }
}
