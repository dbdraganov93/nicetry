<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GeoProxy\Repository\ApiKeyRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
#[ORM\Table(name: 'api_keys')]
#[ORM\UniqueConstraint(name: 'uniq_api_key_hash', columns: ['key_hash'])]
#[ORM\Index(name: 'idx_api_key_active', columns: ['user_id', 'revoked_at'])]
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

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $ipWhitelist = [];

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
    /** @return list<string> */
    public function getIpWhitelist(): array
    {
        return $this->ipWhitelist;
    }
    /** @param list<string> $ipWhitelist */
    public function setIpWhitelist(array $ipWhitelist): self
    {
        $this->ipWhitelist = array_values($ipWhitelist);
        $this->touch();
        return $this;
    }
    public function isIpAllowed(string $ip): bool
    {
        if ($this->ipWhitelist === []) {
            return true;
        }

        foreach ($this->ipWhitelist as $allowed) {
            if ($allowed === $ip || $this->cidrContains($allowed, $ip)) {
                return true;
            }
        }

        return false;
    }
    private function cidrContains(string $cidr, string $ip): bool
    {
        [$range, $prefix] = array_pad(explode('/', $cidr, 2), 2, null);
        if ($prefix === null || !ctype_digit($prefix)) {
            return false;
        }

        $rangePacked = inet_pton($range);
        $ipPacked = inet_pton($ip);
        if ($rangePacked === false || $ipPacked === false || strlen($rangePacked) !== strlen($ipPacked)) {
            return false;
        }

        $bits = (int) $prefix;
        $bytes = intdiv($bits, 8);
        $remainder = $bits % 8;
        if ($bytes > 0 && substr($rangePacked, 0, $bytes) !== substr($ipPacked, 0, $bytes)) {
            return false;
        }
        if ($remainder === 0) {
            return true;
        }

        $mask = (0xff << (8 - $remainder)) & 0xff;
        return (ord($rangePacked[$bytes]) & $mask) === (ord($ipPacked[$bytes]) & $mask);
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
