<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GendarmeRepository")
 * @UniqueEntity(
 *     fields="matricule",
 *     message="Le gendarme avec ce matricule existe déja."
 * )
 */
class Gendarme {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Unit", inversedBy="gendarmes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $unit;

    /**
     * @ORM\Column(type="boolean")
     */
    private $opj;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Grade", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $grade;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="gendarme", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank(message="Le gendarme doit être relié à un utilisateur.")
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentaires;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Section", mappedBy="gendarmes", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $sections;

    /**
     * @ORM\Column(type="integer")
     */
    private $matricule;

    public function __construct()
    {
        $this->opj = FALSE;
        $this->sections = new ArrayCollection();
        try {
            $this->matricule = random_int(100000, 999999);
        }
        catch (\Exception $e) {
        }
    }

    public function __toString()
    {
        if ($this->getUser()->isActif()) $data = " ¤";
        else $data = "";

        return $this->getGrade()->getAbrv()." ".$this->getUser().$data;
    }

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(?Grade $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function isOfficier(): bool
    {
        return $this->getGrade()->getOfficier();
    }

    public function isChef(): bool
    {
        return $this->getUnit()->getChef() === $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function isUnit($abrv)
    {
        return $this->getUnit()->getAbrv() == $abrv;
    }

    public function getOpj(): bool
    {
        return $this->opj;
    }

    public function setOpj(bool $opj): self
    {
        $this->opj = $opj;

        return $this;
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

    /**
     * @return Collection|Section[]
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->addGendarme($this);
        }

        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
            $section->removeGendarme($this);
        }

        return $this;
    }

    public function isSection($abrv)
    {
        if ($abrv == "" or $abrv == NULL) {
            return TRUE;
        }

        foreach ($this->sections as $section) {
            if ($section->getAbrv() == $abrv) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function getMatricule(): ?int
    {
        return $this->matricule;
    }

    public function setMatricule(int $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }
}
