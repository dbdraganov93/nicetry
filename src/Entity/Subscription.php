<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GeoProxy\Repository\SubscriptionRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'subscriptions')]
class Subscription
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'string')]
    private string $status;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $stripeSubscriptionId;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $currentPeriodStart;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $currentPeriodEnd;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Plan::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Plan $plan;

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->status = '';
        $this->stripeSubscriptionId = null;
        $this->currentPeriodStart = new DateTimeImmutable();
        $this->currentPeriodEnd = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): self
    {
        $this->status = $status;
        $this->touch();
        return $this;
    }
    public function getStripeSubscriptionId(): ?string
    {
        return $this->stripeSubscriptionId;
    }
    public function setStripeSubscriptionId(?string $stripeSubscriptionId): self
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;
        $this->touch();
        return $this;
    }
    public function getCurrentPeriodStart(): DateTimeImmutable
    {
        return $this->currentPeriodStart;
    }
    public function setCurrentPeriodStart(DateTimeImmutable $currentPeriodStart): self
    {
        $this->currentPeriodStart = $currentPeriodStart;
        $this->touch();
        return $this;
    }
    public function getCurrentPeriodEnd(): DateTimeImmutable
    {
        return $this->currentPeriodEnd;
    }
    public function setCurrentPeriodEnd(DateTimeImmutable $currentPeriodEnd): self
    {
        $this->currentPeriodEnd = $currentPeriodEnd;
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
