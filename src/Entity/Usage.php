<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GeoProxy\Repository\UsageRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UsageRepository::class)]
#[ORM\Table(name: 'usages')]
#[ORM\Index(name: 'idx_usage_period', columns: ['user_id', 'period_start', 'period_end'])]
class Usage
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $periodStart;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $periodEnd;

    #[ORM\Column(type: 'integer')]
    private int $bytesIn;

    #[ORM\Column(type: 'integer')]
    private int $bytesOut;

    #[ORM\Column(type: 'integer')]
    private int $requestCount;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: ApiKey::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ApiKey $apiKey;

    #[ORM\ManyToOne(targetEntity: Plan::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Plan $plan;

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->periodStart = new DateTimeImmutable();
        $this->periodEnd = new DateTimeImmutable();
        $this->bytesIn = 0;
        $this->bytesOut = 0;
        $this->requestCount = 0;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
    public function getPeriodStart(): DateTimeImmutable
    {
        return $this->periodStart;
    }
    public function setPeriodStart(DateTimeImmutable $periodStart): self
    {
        $this->periodStart = $periodStart;
        $this->touch();
        return $this;
    }
    public function getPeriodEnd(): DateTimeImmutable
    {
        return $this->periodEnd;
    }
    public function setPeriodEnd(DateTimeImmutable $periodEnd): self
    {
        $this->periodEnd = $periodEnd;
        $this->touch();
        return $this;
    }
    public function getBytesIn(): int
    {
        return $this->bytesIn;
    }
    public function setBytesIn(int $bytesIn): self
    {
        $this->bytesIn = $bytesIn;
        $this->touch();
        return $this;
    }
    public function getBytesOut(): int
    {
        return $this->bytesOut;
    }
    public function setBytesOut(int $bytesOut): self
    {
        $this->bytesOut = $bytesOut;
        $this->touch();
        return $this;
    }
    public function getRequestCount(): int
    {
        return $this->requestCount;
    }
    public function setRequestCount(int $requestCount): self
    {
        $this->requestCount = $requestCount;
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
    public function getApiKey(): ?ApiKey
    {
        return $this->apiKey;
    }
    public function setApiKey(?ApiKey $apiKey): self
    {
        $this->apiKey = $apiKey;
        $this->touch();
        return $this;
    }
    public function getPlan(): Plan
    {
        return $this->plan;
    }
    public function setPlan(Plan $plan): self
    {
        $this->plan = $plan;
        $this->touch();
        return $this;
    }
}
