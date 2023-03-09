<?php

namespace App\Entity;

use App\Repository\RunJoinRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RunJoinRequestRepository::class)]
class RunJoinRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'runJoinRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Runner $runner = null;

    #[ORM\ManyToOne(inversedBy: 'runJoinRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Run $run = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRunner(): ?Runner
    {
        return $this->runner;
    }

    public function setRunner(?Runner $runner): self
    {
        $this->runner = $runner;

        return $this;
    }

    public function getRun(): ?Run
    {
        return $this->run;
    }

    public function setRun(?Run $run): self
    {
        $this->run = $run;

        return $this;
    }
}
