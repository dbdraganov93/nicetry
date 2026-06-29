<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GeoProxy\Repository\ExitNodeRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ExitNodeRepository::class)]
#[ORM\Table(name: 'exit_nodes')]
#[ORM\Index(columns: ['healthy', 'active_connections'], name: 'idx_exit_node_health_load')]
class ExitNode
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $uuid;

    /** Public read aliases keep routing code simple and testable without Doctrine. */
    public string $id;
    public string $countryCode;
    public ?string $city;
    public string $vpnContainer;
    public string $proxyContainer;
    public bool $healthy;
    public int $activeConnections;
    public int $weight;

    #[ORM\Column]
    private string $hostname = '';

    #[ORM\Column(nullable: true)]
    private ?string $publicIp = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $lastHeartbeatAt = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Country $country = null;

    #[ORM\ManyToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?City $cityEntity = null;

    public function __construct(
        ?string $id = null,
        string $countryCode = '',
        ?string $city = null,
        string $vpnContainer = '',
        string $proxyContainer = '',
        bool $healthy = false,
        int $activeConnections = 0,
        int $weight = 100,
    ) {
        $this->uuid = $id !== null && Uuid::isValid($id) ? Uuid::fromString($id) : Uuid::v7();
        $this->id = $id ?? (string) $this->uuid;
        $this->countryCode = strtoupper($countryCode);
        $this->city = $city;
        $this->vpnContainer = $vpnContainer;
        $this->proxyContainer = $proxyContainer;
        $this->healthy = $healthy;
        $this->activeConnections = $activeConnections;
        $this->weight = $weight;
    }

    public function getId(): Uuid
    {
        return $this->uuid;
    }
    public function getHostname(): string
    {
        return $this->hostname;
    }
    public function setHostname(string $hostname): self
    {
        $this->hostname = $hostname;
        return $this;
    }
    public function getPublicIp(): ?string
    {
        return $this->publicIp;
    }
    public function setPublicIp(?string $publicIp): self
    {
        $this->publicIp = $publicIp;
        return $this;
    }
    public function getLastHeartbeatAt(): ?DateTimeImmutable
    {
        return $this->lastHeartbeatAt;
    }
    public function heartbeat(?string $publicIp = null): self
    {
        $this->healthy = true;
        $this->publicIp = $publicIp;
        $this->lastHeartbeatAt = new DateTimeImmutable();
        return $this;
    }
    public function getCountry(): ?Country
    {
        return $this->country;
    }
    public function setCountry(?Country $country): self
    {
        $this->country = $country;
        $this->countryCode = $country?->getIsoCode() ?? $this->countryCode;
        return $this;
    }
    public function getCityEntity(): ?City
    {
        return $this->cityEntity;
    }
    public function setCityEntity(?City $city): self
    {
        $this->cityEntity = $city;
        $this->city = $city?->getName();
        return $this;
    }
}
