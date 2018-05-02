<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as Assert2;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PVRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *     fields="numero",
 *     message="Ce numéro de PV existe déjà."
 * )
 */
class PV {
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Gendarme",fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Criminel")
     */
    private $criminels;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="Le contenu ne peut pas être vide.")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le statut ne peut pas être vide.")
     * @Assert\Choice(callback="getPVStatus", message="Le status n'est pas valable.")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gendarme",fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert2\isReferentOPJ
     */
    private $OPJ;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le résumé du PV ne peut pas être vide")
     */
    private $resume;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\Range(
     *     min = 1,
     *     max = 5,
     *     minMessage = "Le niveau minimal est de 1",
     *     maxMessage = "Le niveau maximal est de 5."
     * )
     */
    private $importance;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedDate;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le PV doit avoir un numéro")
     */
    private $numero;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TAJ", mappedBy="PV", fetch="EXTRA_LAZY")
     */
    private $TAJs;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\NotBlank(message="Le niveau de visibilité ne peut pas être vide.")
     * @Assert\Choice(callback="getVisibilities", message="Le niveau de visibilité n'est pas valable.")
     */
    private $visibility;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Prison", mappedBy="PV", fetch="EXTRA_LAZY")
     */
    private $prisons;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Magistrat", fetch="EAGER")
     */
    private $magistrat;

    public function __construct()
    {
        $this->criminels = new ArrayCollection();
        $this->createdDate = new \DateTime();
        $this->updatedDate = new \DateTime();
        $this->status = "En cours";
        $this->importance = 1;
        $this->visibility = 'Tous';
        $this->TAJs = new ArrayCollection();
        $this->prisons = new ArrayCollection();
    }

    public static function getPVStatus()
    {
        return ["En cours", "À modifier", "Transféré", "En cours de jugement", "Terminé", "Autre"];
    }

    public function __toString()
    {
        return $this->getNumero()." | ".$this->getAuthor()." | ".$this->getCreatedDate()->format("d/m/y");
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

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOPJ(): ?Gendarme
    {
        return $this->OPJ;
    }

    public function setOPJ(?Gendarme $OPJ): self
    {
        $this->OPJ = $OPJ;

        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(string $resume): self
    {
        $this->resume = $resume;

        return $this;
    }

    public function getImportance(): ?int
    {
        return $this->importance;
    }

    public function setImportance(int $importance): self
    {
        $this->importance = $importance;

        return $this;
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
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return Collection|TAJ[]
     */
    public function getTAJs(): Collection
    {
        return $this->TAJs;
    }

    public function addTAJ(TAJ $tAJ): self
    {
        if (!$this->TAJs->contains($tAJ)) {
            $this->TAJs[] = $tAJ;
            $tAJ->setPV($this);
        }

        return $this;
    }

    public function removeTAJ(TAJ $tAJ): self
    {
        if ($this->TAJs->contains($tAJ)) {
            $this->TAJs->removeElement($tAJ);
            // set the owning side to null (unless already changed)
            if ($tAJ->getPV() === $this) {
                $tAJ->setPV(NULL);
            }
        }

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getVisibilities(): array
    {
        return ['Tous', 'Unité', 'Perso'];
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return Collection|Prison[]
     */
    public function getPrisons(): Collection
    {
        return $this->prisons;
    }

    public function addPrison(Prison $prison): self
    {
        if (!$this->prisons->contains($prison)) {
            $this->prisons[] = $prison;
            $prison->setPV($this);
        }

        return $this;
    }

    public function removePrison(Prison $prison): self
    {
        if ($this->prisons->contains($prison)) {
            $this->prisons->removeElement($prison);
            // set the owning side to null (unless already changed)
            if ($prison->getPV() === $this) {
                $prison->setPV(NULL);
            }
        }

        return $this;
    }

    public function getMagistrat(): ?Magistrat
    {
        return $this->magistrat;
    }

    public function setMagistrat(?Magistrat $magistrat): self
    {
        $this->magistrat = $magistrat;

        return $this;
    }
}
