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

    #[ORM\ManyToMany(targetEntity: Runner::class, inversedBy: 'Run', cascade: ["remove"])]
    private Collection $runner;

    #[ORM\OneToMany(mappedBy: 'run', targetEntity: RunJoinRequest::class, orphanRemoval: true)]
    private Collection $runJoinRequests;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finished_at = null;

    public function __construct()
    {
        $this->runner = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->runJoinRequests = new ArrayCollection();
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

    public function addRunner(Runner $runner): self
    {
        if (!$this->runner->contains($runner)) {
            $this->runner->add($runner);
        }

        return $this;
    }

    public function removeRunner(Runner $runner): self
    {
        $this->runner->removeElement($runner);

        return $this;
    }

    /**
     * @return Collection<int, RunJoinRequest>
     */
    public function getRunJoinRequests(): Collection
    {
        return $this->runJoinRequests;
    }

    public function addRunJoinRequest(RunJoinRequest $runJoinRequest): self
    {
        if (!$this->runJoinRequests->contains($runJoinRequest)) {
            $this->runJoinRequests->add($runJoinRequest);
            $runJoinRequest->setRun($this);
        }

        return $this;
    }

    public function removeRunJoinRequest(RunJoinRequest $runJoinRequest): self
    {
        if ($this->runJoinRequests->removeElement($runJoinRequest)) {
            // set the owning side to null (unless already changed)
            if ($runJoinRequest->getRun() === $this) {
                $runJoinRequest->setRun(null);
            }
        }

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finished_at;
    }

    public function setFinishedAt(?\DateTimeImmutable $finished_at): self
    {
        $this->finished_at = $finished_at;

        return $this;
    }
}
