<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GradeRepository")
 */
class Grade
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le nom du grade ne peut pas être vide.")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank(message="L'abbréviation ne peut pas être vide.")
     */
    private $abrv;

    /**
     * @ORM\Column(type="boolean")
     */
    private $officier;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    public function __toString()
    {
        return $this->getName();
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

    public function getAbrv(): ?string
    {
        return $this->abrv;
    }

    public function setAbrv(string $abrv): self
    {
        $this->abrv = $abrv;

        return $this;
    }

    public function getOfficier(): ?bool
    {
        return $this->officier;
    }

    public function setOfficier(bool $officier): self
    {
        $this->officier = $officier;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }
}
