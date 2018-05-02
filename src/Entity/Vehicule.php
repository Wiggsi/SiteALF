<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VehiculeRepository")
 * @UniqueEntity(
 *     fields="plaque",
 *     message="Cette plaque existe déja."
 * )
 */
class Vehicule
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex(
     *     pattern="/^(|^[A-Z]{2}[-][0-9]{3}[-][A-Z]{2}$)$/",
     *     message="La plaque doit être valide."
     * )
     */
    private $plaque;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom du prorpiétaire ne peut pas être vide.")
     */
    private $propName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le prénom du propriétaire ne peut pas être vide.")
     */
    private $propFirstName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gendarme")
     */
    private $author;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le type de véhicule doit être spécifié.")
     */
    private $type;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function __toString()
    {
        return $this->getPlaque();
    }

    public function getPlaque(): ?string
    {
        return $this->plaque;
    }

    public function setPlaque(?string $plaque): self
    {
        $this->plaque = $plaque;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPropName(): ?string
    {
        return $this->propName;
    }

    public function setPropName(string $propName): self
    {
        $this->propName = $propName;

        return $this;
    }

    public function getPropFirstName(): ?string
    {
        return $this->propFirstName;
    }

    public function setPropFirstName(string $propFirstName): self
    {
        $this->propFirstName = $propFirstName;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAuthor(): ?Gendarme
    {
        return $this->author;
    }

    public function setAuthor(?Gendarme $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
