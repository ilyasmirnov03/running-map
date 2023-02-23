<?php

namespace App\Entity;

use App\Repository\RunRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RunRepository::class)]
class Run
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255)]
    private ?string $map = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $run_date = null;

    #[ORM\ManyToMany(targetEntity: runner::class, inversedBy: 'runs')]
    private Collection $runner;

    public function __construct()
    {
        $this->runner = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getMap(): ?string
    {
        return $this->map;
    }

    public function setMap(string $map): self
    {
        $this->map = $map;

        return $this;
    }

    public function getRunDate(): ?\DateTimeInterface
    {
        return $this->run_date;
    }

    public function setRunDate(\DateTimeInterface $run_date): self
    {
        $this->run_date = $run_date;

        return $this;
    }

    /**
     * @return Collection<int, runner>
     */
    public function getRunners(): Collection
    {
        return $this->runner;
    }

    public function addRunner(runner $runner): self
    {
        if (!$this->runner->contains($runner)) {
            $this->runner->add($runner);
        }

        return $this;
    }

    public function removeRunner(runner $runner): self
    {
        $this->runner->removeElement($runner);

        return $this;
    }
}
