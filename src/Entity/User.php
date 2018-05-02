<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as Assert2;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 *
 * @UniqueEntity(
 *     fields="email",
 *     message="Cet email est déja utilisé."
 * )
 * @UniqueEntity(
 *     fields="username",
 *     message="Cet utilisateur existe déja."
 * )
 *
 * @Assert2\Registration()
 */
class User implements UserInterface, \Serializable {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le prénom ne peut pas être vide.")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le nom ne peut pas être vide.")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     *
     * @Assert\NotBlank(message="Le nom d'utilisateur ne peut pas être vide.")
     */
    private $username;

    /**
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank(message="La date de naissance ne peut pas être vide.")
     */
    private $birthdate;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Gendarme", mappedBy="user", cascade={"persist", "remove"})
     */
    private $gendarme;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="L'email ne peut pas être vide.")
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le mot de passe ne peut pas être vide.")
     */
    private $password;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdDateTime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $blocked;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastActivity;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Magistrat", mappedBy="user", cascade={"persist", "remove"})
     */
    private $magistrat;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Gardien", mappedBy="user", cascade={"persist", "remove"})
     */
    private $gardien;

    public function __construct()
    {
        $this->roles = ["ROLE_USER"];
        $this->createdDateTime = new \DateTime();
        $this->birthdate = new \DateTime();
        $this->lastActivity = new \DateTime();
        $this->blocked = FALSE;
    }

    public function __toString()
    {
        return $this->getFirstName().' '.mb_strtoupper($this->getName());
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
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

    public function getId()
    {
        return $this->id;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function isGendarme(): bool
    {
        return $this->gendarme != NULL;
    }

    public function getGendarme(): ?Gendarme
    {
        return $this->gendarme;
    }

    public function setGendarme(?Gendarme $gendarme): self
    {
        $this->gendarme = $gendarme;

        // set the owning side of the relation if necessary
        if ($this !== $gendarme->getUser()) {
            $gendarme->setUser($this);
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): ?array
    {
        return array_unique($this->roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt(): ?string
    {
        return NULL;
    }

    public function eraseCredentials(): void
    {

    }

    public function serialize(): string
    {
        return serialize([$this->id, $this->getUsername(), $this->password]);
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $name): self
    {
        $this->username = $name;

        return $this;
    }

    public function unserialize($serialized): void
    {
        [$this->id, $this->username, $this->password] = unserialize($serialized, ['allowed_classes' => FALSE]);
    }

    public function getCreatedDateTime(): ?\DateTimeInterface
    {
        return $this->createdDateTime;
    }

    public function setCreatedDateTime(\DateTimeInterface $createdDateTime): self
    {
        $this->createdDateTime = $createdDateTime;

        return $this;
    }

    public function getBlocked(): ?bool
    {
        return $this->blocked;
    }

    public function setBlocked(bool $blocked): self
    {
        $this->blocked = $blocked;

        return $this;
    }

    public function getLastActivity(): ?\DateTime
    {
        return $this->lastActivity;
    }

    public function setLastActivity(\DateTimeInterface $lastActivity): self
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    public function getMagistrat(): ?Magistrat
    {
        return $this->magistrat;
    }

    public function isMagistrat(): bool
    {
        return $this->magistrat != NULL;
    }

    public function setMagistrat(Magistrat $magistrat): self
    {
        $this->magistrat = $magistrat;

        // set the owning side of the relation if necessary
        if ($this !== $magistrat->getUser()) {
            $magistrat->setUser($this);
        }

        return $this;
    }

    public function getGardien(): ?Gardien
    {
        return $this->gardien;
    }

    public function isGardien(): bool
    {
        return $this->gardien != NULL;
    }

    public function setGardien(Gardien $gardien): self
    {
        $this->gardien = $gardien;

        // set the owning side of the relation if necessary
        if ($this !== $gardien->getUser()) {
            $gardien->setUser($this);
        }

        return $this;
    }

    public function isActif(): bool
    {
        $delay = new \DateTime('2 minutes ago');

        return ($this->getLastActivity() > $delay);
    }
}
