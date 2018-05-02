<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UnitRepository")
 */
class Unit {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le nom de l'unité ne peut pas être vide.")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="L'abbréviation ne peut pas être vide.")
     */
    private $abrv;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Gendarme", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $chef;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icon;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Gendarme", mappedBy="unit", fetch="EXTRA_LAZY")
     */
    private $gendarmes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    public function __construct()
    {
        $this->gendarmes = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getAbrv();
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

    public function getId()
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

    /**
     * @return Collection|Gendarme[]
     */
    public function getGendarmes(): Collection
    {
        return $this->gendarmes;
    }

    public function addGendarme(Gendarme $gendarme): self
    {
        if (!$this->gendarmes->contains($gendarme)) {
            $this->gendarmes[] = $gendarme;
            $gendarme->setUnit($this);
        }

        return $this;
    }

    public function removeGendarme(Gendarme $gendarme): self
    {
        if ($this->gendarmes->contains($gendarme)) {
            $this->gendarmes->removeElement($gendarme);
            // set the owning side to null (unless already changed)
            if ($gendarme->getUnit() === $this) {
                $gendarme->setUnit(NULL);
            }
        }

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

    public function getChef(): ?Gendarme
    {
        return $this->chef;
    }

    public function setChef(Gendarme $chef): self
    {
        $this->chef = $chef;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

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
