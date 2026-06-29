<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GeoProxy\Repository\PlanRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
#[ORM\Table(name: 'plans')]
class Plan
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $stripePriceId;

    #[ORM\Column(type: 'integer')]
    private int $monthlyQuotaBytes;

    #[ORM\Column(type: 'integer')]
    private int $priceCents;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->name = '';
        $this->stripePriceId = null;
        $this->monthlyQuotaBytes = 0;
        $this->priceCents = 0;
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
    public function getStripePriceId(): ?string
    {
        return $this->stripePriceId;
    }
    public function setStripePriceId(?string $stripePriceId): self
    {
        $this->stripePriceId = $stripePriceId;
        $this->touch();
        return $this;
    }
    public function getMonthlyQuotaBytes(): int
    {
        return $this->monthlyQuotaBytes;
    }
    public function setMonthlyQuotaBytes(int $monthlyQuotaBytes): self
    {
        $this->monthlyQuotaBytes = $monthlyQuotaBytes;
        $this->touch();
        return $this;
    }
    public function getPriceCents(): int
    {
        return $this->priceCents;
    }
    public function setPriceCents(int $priceCents): self
    {
        $this->priceCents = $priceCents;
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
}
