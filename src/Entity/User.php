<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'createdBy')]
    private Collection $tasksCreated;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'assignedTo')]
    private Collection $tasksAssigned;

    public function __construct()
    {
        $this->tasksCreated = new ArrayCollection();
        $this->tasksAssigned = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasksCreated(): Collection
    {
        return $this->tasksCreated;
    }

    public function addTasksCreated(Task $tasksCreated): static
    {
        if (!$this->tasksCreated->contains($tasksCreated)) {
            $this->tasksCreated->add($tasksCreated);
            $tasksCreated->setCreatedBy($this);
        }

        return $this;
    }

    public function removeTasksCreated(Task $tasksCreated): static
    {
        if ($this->tasksCreated->removeElement($tasksCreated)) {
            // set the owning side to null (unless already changed)
            if ($tasksCreated->getCreatedBy() === $this) {
                $tasksCreated->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasksAssigned(): Collection
    {
        return $this->tasksAssigned;
    }

    public function addTasksAssigned(Task $tasksAssigned): static
    {
        if (!$this->tasksAssigned->contains($tasksAssigned)) {
            $this->tasksAssigned->add($tasksAssigned);
            $tasksAssigned->setAssignedTo($this);
        }

        return $this;
    }

    public function removeTasksAssigned(Task $tasksAssigned): static
    {
        if ($this->tasksAssigned->removeElement($tasksAssigned)) {
            // set the owning side to null (unless already changed)
            if ($tasksAssigned->getAssignedTo() === $this) {
                $tasksAssigned->setAssignedTo(null);
            }
        }

        return $this;
    }
}
