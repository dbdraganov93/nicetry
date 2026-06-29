<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GeoProxy\Repository\ProxyCredentialRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProxyCredentialRepository::class)]
#[ORM\Table(name: 'proxy_credentials')]
class ProxyCredential
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'string')]
    private string $username;

    #[ORM\Column(type: 'string')]
    private string $passwordHash;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->username = '';
        $this->passwordHash = '';
        $this->active = false;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
    public function getUsername(): string
    {
        return $this->username;
    }
    public function setUsername(string $username): self
    {
        $this->username = $username;
        $this->touch();
        return $this;
    }
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;
        $this->touch();
        return $this;
    }
    public function getActive(): bool
    {
        return $this->active;
    }
    public function setActive(bool $active): self
    {
        $this->active = $active;
        $this->touch();
        return $this;
    }
    public function getUser(): User
    {
        return $this->user;
    }
    public function setUser(User $user): self
    {
        $this->user = $user;
        $this->touch();
        return $this;
    }
}
