<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GeoProxy\Repository\ApiKeyRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
#[ORM\Table(name: 'api_keys')]
class ApiKey
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $keyHash;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastUsedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $revokedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->name = '';
        $this->keyHash = '';
        $this->lastUsedAt = null;
        $this->revokedAt = null;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): self
    {
        $this->name = $name;
        $this->touch();
        return $this;
    }
    public function getKeyHash(): string
    {
        return $this->keyHash;
    }
    public function setKeyHash(string $keyHash): self
    {
        $this->keyHash = $keyHash;
        $this->touch();
        return $this;
    }
    public function getLastUsedAt(): ?DateTimeImmutable
    {
        return $this->lastUsedAt;
    }
    public function setLastUsedAt(?DateTimeImmutable $lastUsedAt): self
    {
        $this->lastUsedAt = $lastUsedAt;
        $this->touch();
        return $this;
    }
    public function getRevokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }
    public function setRevokedAt(?DateTimeImmutable $revokedAt): self
    {
        $this->revokedAt = $revokedAt;
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
