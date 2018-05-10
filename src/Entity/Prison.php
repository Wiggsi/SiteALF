<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as Assert2;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PrisonRepository")
 *
 * @Assert2\CanGoInPrison()
 */
class Prison {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Criminel")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank(message="Le crimiel doit être spécifié.")
     */
    private $criminel;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank(message="La date de début d'incarcération ne peut pas être nulle.")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank(message="La date de fin d'incarcération ne peut pas être nulle.")
     */
    private $endDate;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\Choice(callback="getTypes", message="Le type d'incarcération n'est pas valable.")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gendarme")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PV", inversedBy="prisons")
     */
    private $PV;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ended;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Choice(callback="getValidations", message="Le type de contrôle judiciaire n'est pas valable.")
     */
    private $validation;

    /**
     * @ORM\Column(type="datetime")
     */
    private $validationDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enAttente;

    /**
     * @ORM\Column(type="boolean")
     */
    private $evaded;

    public function __construct()
    {
        $this->startDate = new \DateTime();
        $this->endDate = new \DateTime("+2days");
        $this->validationDate = new \DateTime();
        $this->ended = FALSE;
        $this->evaded = FALSE;
        $this->validation = "Jamais";
    }

    public function __toString()
    {
        if ($this->ended) $data2 = " - Terminé";
        else if ($this->evaded) $data2 = " - Évadé";
        else $data2 = "";

        return $this->getCriminel().' - '.$this->getType().' - '.$this->getStatus().' | '.$this->getStartDate()->format('d/m').$data2;
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

    public function getCriminel(): ?Criminel
    {
        return $this->criminel;
    }

    public function setCriminel(?Criminel $criminel): self
    {
        $this->criminel = $criminel;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getTypes()
    {
        return ['GAV', 'Bracelet électronique', 'Prison', 'Autre', 'Évadé'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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

    public function getPV(): ?PV
    {
        return $this->PV;
    }

    public function setPV(?PV $PV): self
    {
        $this->PV = $PV;

        return $this;
    }

    public function getEnded(): ?bool
    {
        return $this->ended;
    }

    public function setEnded(bool $ended): self
    {
        $this->ended = $ended;

        return $this;
    }

    public function isPrison(): bool
    {
        return $this->type == "Prison";
    }

    public function isGendarmerie(): bool
    {
        return $this->type == "GAV" or $this->type == "Bracelet électronique" or $this->type == "Autre" or $this->type == "Évadé";
    }

    public function getValidation(): ?string
    {
        return $this->validation;
    }

    public function setValidation(?string $validation): self
    {
        $this->validation = $validation;

        return $this;
    }

    public function getValidationDate(): ?\DateTimeInterface
    {
        return $this->validationDate;
    }

    public function setValidationDate(\DateTimeInterface $validationDate): self
    {
        $this->validationDate = $validationDate;

        return $this;
    }

    public function getValidations()
    {
        return ['24h',
            '48h',
            '72h',
            'Semaine',
            'Jamais'];
    }

    public function isValid()
    {
        $duree = $this->validationDate->diff(new \Datetime(), TRUE);
        if ($this->validation != "Jamais" and $duree->m != 0)
            return FALSE;
        else if ($this->validation == "24h" and $duree->d >= 1)
            return FALSE;
        else if ($this->validation == "48h" and $duree->d >= 2)
            return FALSE;
        else if ($this->validation == "72h" and $duree->d >= 3)
            return FALSE;
        else if ($this->validation == "Semaine" and $duree->d >= 7)
            return FALSE;
        else
            return TRUE;
    }

    public function getDuree(): \DateInterval
    {
        return $this->startDate->diff(new \DateTime(), TRUE);
    }

    public function getEnAttente(): ?bool
    {
        return $this->enAttente;
    }

    public function getStatus(): string
    {
        if ($this->enAttente) return "En attente de jugement";
        else return "Condamné";
    }

    public function setEnAttente(bool $enAttente): self
    {
        $this->enAttente = $enAttente;

        return $this;
    }

    public function getEvaded(): ?bool
    {
        return $this->evaded;
    }

    public function setEvaded(bool $evaded): self
    {
        $this->evaded = $evaded;

        return $this;
    }
}
