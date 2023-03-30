<?php

namespace App\Entity;

use App\Repository\RunnerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: RunnerRepository::class)]
class Runner implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $login = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $picture = null;

    #[ORM\OneToMany(mappedBy: 'Runner', targetEntity: Coordinates::class)]
    private Collection $coords;

    #[ORM\ManyToMany(targetEntity: Run::class, mappedBy: 'runner')]
    private Collection $runs;

    #[ORM\OneToMany(mappedBy: 'runner', targetEntity: RunJoinRequest::class, orphanRemoval: true)]
    private Collection $runJoinRequests;

    public function __construct()
    {
        $this->coords = new ArrayCollection();
        $this->runs = new ArrayCollection();
        $this->runJoinRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_RUNNER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return Collection<int, Coordinates>
     */
    public function getCoords(): Collection
    {
        return $this->coords;
    }

    public function addCoord(Coordinates $coord): self
    {
        if (!$this->coords->contains($coord)) {
            $this->coords->add($coord);
            $coord->setRunner($this);
        }

        return $this;
    }

    public function removeCoord(Coordinates $coord): self
    {
        if ($this->coords->removeElement($coord)) {
            // set the owning side to null (unless already changed)
            if ($coord->getRunner() === $this) {
                $coord->setRunner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Run>
     */
    public function getRuns(): Collection
    {
        return $this->runs;
    }

    public function addRun(Run $run): self
    {
        if (!$this->runs->contains($run)) {
            $this->runs->add($run);
            $run->addRunner($this);
        }

        return $this;
    }

    public function removeRun(Run $run): self
    {
        if ($this->runs->removeElement($run)) {
            $run->removeRunner($this);
        }

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
            $runJoinRequest->setRunner($this);
        }

        return $this;
    }

    public function removeRunJoinRequest(RunJoinRequest $runJoinRequest): self
    {
        if ($this->runJoinRequests->removeElement($runJoinRequest)) {
            // set the owning side to null (unless already changed)
            if ($runJoinRequest->getRunner() === $this) {
                $runJoinRequest->setRunner(null);
            }
        }

        return $this;
    }
}
