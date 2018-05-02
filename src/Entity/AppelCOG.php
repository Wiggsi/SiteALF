<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\AppelCOGRepository")
 */
class AppelCOG
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gendarme")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le nom de l'appelant ne peut pas être vide. (Inconnu sinon)")
     */
    private $name;


    //Regex acceptant les numéros nuls : /^(^(0)[1-9][0-9]{8}|)$/

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank(message="Le numéro ne peut pas être vide.")
     * @Assert\Regex(
     *     pattern="/^(0)[1-9][0-9]{8}$/",
     *     message="Le numéro de téléphone doit être valide."
     * )
     */
    private $tel;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="Le contenu de l'appel ne peut pas être vide.")
     */
    private $content;

    public function __construct()
    {
        $this->createdDate = new \DateTime();
    }

    public function __toString()
    {
        return $this->getCreatedDate()->format('d/m/y').' | '.$this->getName().' '.$this->getFirstName();
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getId()
    {
        return $this->id;
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

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
