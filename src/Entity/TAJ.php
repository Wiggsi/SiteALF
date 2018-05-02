<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TAJRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class TAJ {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Criminel")
     * @Assert\NotBlank(message="L'entrée doit concerner au moins un individu.")
     */
    private $criminels;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @ORM\Column(type="array")
     * @Assert\NotBlank(message="Une entrée au TAJ doit comporter des infractions.")
     */
    private $infractions;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PV", inversedBy="TAJs")
     */
    private $PV;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gendarme")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    public function __construct()
    {
        $this->criminels = new ArrayCollection();
        $this->Infractions = new ArrayCollection();
        $this->createdDate = new \DateTime();
        $this->updatedDate = new \DateTime();
    }

    public function __toString()
    {
//        return 'n°'.$this->getId().' - '.$this->getUpdatedDate()->format('d/m/y H:m');
        return $this->getUpdatedDate()->format('d/m/y H:m');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    /**
     * @return Collection|Criminel[]
     */
    public function getCriminels(): Collection
    {
        return $this->criminels;
    }

    public function addCriminel(Criminel $criminel): self
    {
        if (!$this->criminels->contains($criminel)) {
            $this->criminels[] = $criminel;
        }

        return $this;
    }

    public function removeCriminel(Criminel $criminel): self
    {
        if ($this->criminels->contains($criminel)) {
            $this->criminels->removeElement($criminel);
        }

        return $this;
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

//    /**
//     * @return Collection|Infraction[]
//     */
//    public function getInfractions(): Collection
//    {
//        return $this->Infractions;
//    }
//
//    public function addInfraction(Infraction $infraction): self
//    {
//        if (!$this->Infractions->contains($infraction)) {
//            $this->Infractions[] = $infraction;
//        }
//
//        return $this;
//    }
//
//    public function removeInfraction(Infraction $infraction): self
//    {
//        if ($this->Infractions->contains($infraction)) {
//            $this->Infractions->removeElement($infraction);
//        }
//
//        return $this;
//    }

    public function getInfractions(): ?array
    {
        return $this->infractions;
    }

    public function setInfractions(array $infractions): self
    {
        $this->infractions = $infractions;

        return $this;
    }


    public function getPV(): ?PV
    {
        return $this->PV;
    }

    public function setPV(?PV $PV): self
    {
        $this->PV = $PV;

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        $this->setUpdatedDate(new \Datetime());
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
}
