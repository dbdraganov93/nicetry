<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GeoProxy\Repository\ProxyNodeRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProxyNodeRepository::class)]
#[ORM\Table(name: 'proxy_nodes')]
class ProxyNode
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'string')]
    private string $hostname;

    #[ORM\Column(type: 'string')]
    private string $listenHost;

    #[ORM\Column(type: 'integer')]
    private int $httpPort;

    #[ORM\Column(type: 'integer')]
    private int $httpsPort;

    #[ORM\Column(type: 'boolean')]
    private bool $healthy;

    #[ORM\Column(type: 'integer')]
    private int $activeConnections;

    #[ORM\ManyToOne(targetEntity: ExitNode::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ExitNode $exitNode;

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->hostname = '';
        $this->listenHost = '';
        $this->httpPort = 0;
        $this->httpsPort = 0;
        $this->healthy = false;
        $this->activeConnections = 0;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
    public function getHostname(): string
    {
        return $this->hostname;
    }
    public function setHostname(string $hostname): self
    {
        $this->hostname = $hostname;
        $this->touch();
        return $this;
    }
    public function getListenHost(): string
    {
        return $this->listenHost;
    }
    public function setListenHost(string $listenHost): self
    {
        $this->listenHost = $listenHost;
        $this->touch();
        return $this;
    }
    public function getHttpPort(): int
    {
        return $this->httpPort;
    }
    public function setHttpPort(int $httpPort): self
    {
        $this->httpPort = $httpPort;
        $this->touch();
        return $this;
    }
    public function getHttpsPort(): int
    {
        return $this->httpsPort;
    }
    public function setHttpsPort(int $httpsPort): self
    {
        $this->httpsPort = $httpsPort;
        $this->touch();
        return $this;
    }
    public function getHealthy(): bool
    {
        return $this->healthy;
    }
    public function setHealthy(bool $healthy): self
    {
        $this->healthy = $healthy;
        $this->touch();
        return $this;
    }
    public function getActiveConnections(): int
    {
        return $this->activeConnections;
    }
    public function setActiveConnections(int $activeConnections): self
    {
        $this->activeConnections = $activeConnections;
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
}
