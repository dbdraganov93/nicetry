<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GeoProxy\Repository\CountryRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ORM\Table(name: 'countrys')]
class Country
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'string')]
    private string $isoCode;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled;

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->isoCode = '';
        $this->name = '';
        $this->enabled = false;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
    public function getIsoCode(): string
    {
        return $this->isoCode;
    }
    public function setIsoCode(string $isoCode): self
    {
        $this->isoCode = $isoCode;
        $this->touch();
        return $this;
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
    public function getEnabled(): bool
    {
        return $this->enabled;
    }
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        $this->touch();
        return $this;
    }
}
