<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SectionRepository")
 */
class Section {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icon;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Gendarme", inversedBy="sections", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $gendarmes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $abrv;

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

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
        }

        return $this;
    }

    public function removeGendarme(Gendarme $gendarme): self
    {
        if ($this->gendarmes->contains($gendarme)) {
            $this->gendarmes->removeElement($gendarme);
        }

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
