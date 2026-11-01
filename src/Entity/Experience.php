<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Experience
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private string $company;

    #[ORM\Column]
    private string $role;

    #[ORM\Column]
    private string $location;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $startDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $missions = [];

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $stack = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getMissions(): array
    {
        return $this->missions;
    }

    /**
     * @param list<string> $missions
     */
    public function setMissions(array $missions): static
    {
        $this->missions = $missions;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getStack(): array
    {
        return $this->stack;
    }

    /**
     * @param list<string> $stack
     */
    public function setStack(array $stack): static
    {
        $this->stack = $stack;

        return $this;
    }
}
