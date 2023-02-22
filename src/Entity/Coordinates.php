<?php

namespace App\Entity;

use App\Repository\CoordinatesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    #[ORM\JoinColumn(nullable: false)]
    private ?runner $runner = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8)]
    private ?string $longitude = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $coords_date = null;

    #[ORM\ManyToMany(targetEntity: run::class)]
    private Collection $run;

    public function __construct()
    {
        $this->run = new ArrayCollection();
    }

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

    public function getCoordsDate(): ?\DateTimeInterface
    {
        return $this->coords_date;
    }

    public function setCoordsDate(\DateTimeInterface $coords_date): self
    {
        $this->coords_date = $coords_date;

        return $this;
    }

    /**
     * @return Collection<int, run>
     */
    public function getRun(): Collection
    {
        return $this->run;
    }

    public function addRun(run $run): self
    {
        if (!$this->run->contains($run)) {
            $this->run->add($run);
        }

        return $this;
    }

    public function removeRun(run $run): self
    {
        $this->run->removeElement($run);

        return $this;
    }
}
