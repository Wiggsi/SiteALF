<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CriminelRepository")
 * @UniqueEntity(
 *     fields={"firstName", "name"},
 *     errorPath="name",
 *     message="Ce TAJ existe déja."
 * )
 */
class Criminel {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthdate;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le prénom ne peut pas être vide.")
     * @Assert\Length(
     *      min = 3,
     *      max = 70,
     *      minMessage = "Le prénom doit faire au minimum {{ limit }} caractères.",
     *      maxMessage = "Le prénom doit faire au maximum {{ limit }} caractères."
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le nom ne peut pas être vide.")
     * @Assert\Length(
     *      min = 3,
     *      max = 70,
     *      minMessage = "Le nom doit faire au minimum {{ limit }} caractères.",
     *      maxMessage = "Le nom doit faire au maximum {{ limit }} caractères."
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $wanted;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(message="Le niveau de dangerosité ne peut pas être nul.")
     * @Assert\Range(
     *     min=0,
     *     max=10,
     *     minMessage = "Le niveau de danger minimal est 0.",
     *     maxMessage = "Le niveau de danger maximal est 10.",
     *     invalidMessage="Le niveau de dangerosité doit être un nomre entre 0 et 10."
     * )
     */
    private $dangerous;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prison", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $fichePrison;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentaires;

    /**
     * @ORM\Column(type="string", nullable=true, length=10)
     * @Assert\Regex(
     *     pattern="/^(^(0)[1-9][0-9]{8}|)$/",
     *     message="Le numéro de téléphone doit être valide."
     * )
     */
    private $tel;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ADNCode;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $photoCode;

    public function __construct()
    {
        $this->wanted = FALSE;
        $this->dangerous = 0;
    }

    public static function getPrisonStatus()
    {
        return ["Libre", "GAV", "Bracelet électronique", "Prison", "Évadé"];
    }

    public function __toString()
    {
        return $this->getFirstName()." ".strtoupper($this->getName());
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

    public function setBirthdate($birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getWanted(): ?bool
    {
        return $this->wanted;
    }

    public function setWanted(bool $wanted): self
    {
        $this->wanted = $wanted;

        return $this;
    }

    public function getDangerous(): ?int
    {
        return $this->dangerous;
    }

    public function setDangerous(int $dangerous): self
    {
        $this->dangerous = $dangerous;

        return $this;
    }

    public function isEvade(): bool
    {
        if ($this->fichePrison == NULL) return FALSE;
        else {
            if ($this->getFichePrison()->getEvaded()) return TRUE;
            else return FALSE;
        }
    }

    public function isInPrison(): bool
    {
        if ($this->fichePrison == NULL) return FALSE;
        else {
            if ($this->getFichePrison()->getType() == "Prison") return TRUE;
            else return FALSE;
        }
    }

    public function isInGAV(): bool
    {
        if ($this->fichePrison == NULL) return FALSE;
        else {
            if ($this->getFichePrison()->getType() == "GAV") return TRUE;
            else return FALSE;
        }
    }

    public function hasBracelet(): bool
    {
        if ($this->fichePrison == NULL) return FALSE;
        else {
            if ($this->getFichePrison()->getType() == "Bracelet électronique") return TRUE;
            else return FALSE;
        }
    }

    public function isFree(): ?bool
    {
        return $this->fichePrison == NULL;
    }

    public function getCommentaires(): ?string
    {
        return $this->commentaires;
    }

    public function setCommentaires(?string $commentaires): self
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getADNCode(): ?int
    {
        return $this->ADNCode;
    }

    public function setADNCode(?int $ADNCode): self
    {
        $this->ADNCode = $ADNCode;

        return $this;
    }

    public function getPhotoCode(): ?int
    {
        return $this->photoCode;
    }

    public function setPhotoCode(?int $photoCode): self
    {
        $this->photoCode = $photoCode;

        return $this;
    }

    public function getFichePrison(): ?Prison
    {
        return $this->fichePrison;
    }

    public function setFichePrison(?Prison $prison): self
    {
        $this->fichePrison = $prison;

        return $this;
    }
}