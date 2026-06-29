<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GeoProxy\Repository\RequestLogRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RequestLogRepository::class)]
#[ORM\Table(name: 'request_logs')]
#[ORM\Index(name: 'idx_request_log_user_created', columns: ['user_id', 'created_at'])]
class RequestLog
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'string')]
    private string $protocol;

    #[ORM\Column(type: 'string')]
    private string $targetHost;

    #[ORM\Column(type: 'integer')]
    private int $targetPort;

    #[ORM\Column(type: 'integer')]
    private int $bytesIn;

    #[ORM\Column(type: 'integer')]
    private int $bytesOut;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $statusCode;

    #[ORM\Column(type: 'integer')]
    private int $durationMs;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: ApiKey::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ApiKey $apiKey;

    #[ORM\ManyToOne(targetEntity: ExitNode::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ExitNode $exitNode;

    #[ORM\ManyToOne(targetEntity: ProxyNode::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ProxyNode $proxyNode;

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->protocol = '';
        $this->targetHost = '';
        $this->targetPort = 0;
        $this->bytesIn = 0;
        $this->bytesOut = 0;
        $this->statusCode = null;
        $this->durationMs = 0;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
    public function getProtocol(): string
    {
        return $this->protocol;
    }
    public function setProtocol(string $protocol): self
    {
        $this->protocol = $protocol;
        $this->touch();
        return $this;
    }
    public function getTargetHost(): string
    {
        return $this->targetHost;
    }
    public function setTargetHost(string $targetHost): self
    {
        $this->targetHost = $targetHost;
        $this->touch();
        return $this;
    }
    public function getTargetPort(): int
    {
        return $this->targetPort;
    }
    public function setTargetPort(int $targetPort): self
    {
        $this->targetPort = $targetPort;
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
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
    public function setStatusCode(?int $statusCode): self
    {
        $this->statusCode = $statusCode;
        $this->touch();
        return $this;
    }
    public function getDurationMs(): int
    {
        return $this->durationMs;
    }
    public function setDurationMs(int $durationMs): self
    {
        $this->durationMs = $durationMs;
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
    public function getExitNode(): ExitNode
    {
        return $this->exitNode;
    }
    public function setExitNode(ExitNode $exitNode): self
    {
        $this->exitNode = $exitNode;
        $this->touch();
        return $this;
    }
    public function getProxyNode(): ?ProxyNode
    {
        return $this->proxyNode;
    }
    public function setProxyNode(?ProxyNode $proxyNode): self
    {
        $this->proxyNode = $proxyNode;
        $this->touch();
        return $this;
    }
}
