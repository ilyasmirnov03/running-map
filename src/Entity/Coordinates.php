<?php

namespace App\Entity;

use App\Repository\CoordinatesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoordinatesRepository::class)]
class Coordinates
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'coordinates')]
    #[ORM\JoinColumn(nullable: false, onDelete: "cascade")]
    private ?runner $runner = null;

    #[ORM\ManyToOne(inversedBy: 'coordinates')]
    #[ORM\JoinColumn(nullable: false, onDelete: "cascade")]
    private ?run $run = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8)]
    private ?string $longitude = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $coords_date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRunner(): ?runner
    {
        return $this->runner;
    }

    public function setRunner(?runner $runner): self
    {
        $this->runner = $runner;

        return $this;
    }

    public function getRun(): ?run
    {
        return $this->run;
    }

    public function setRun(?run $run): self
    {
        $this->run = $run;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getCoordsDate(): ?\DateTimeImmutable
    {
        return $this->coords_date;
    }

    public function setCoordsDate(\DateTimeImmutable $coords_date): self
    {
        $this->coords_date = $coords_date;

        return $this;
    }
}
