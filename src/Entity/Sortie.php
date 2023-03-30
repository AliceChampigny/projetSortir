<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use http\Message;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotNull(null,
        message: 'Le nom ne peut pas être null'
    )]
    #[Assert\NotBlank(null,
        message: 'Le nom ne peut pas être vide'
    )]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(null,
        message: "Le date et l'heure de début ne peuvent pas être null"
    )]
    #[Assert\NotBlank(null,
        message: "Le date et l'heure de début ne peuvent pas être vides"
    )]
    #[Assert\GreaterThanOrEqual('+ 1 days',
    message: 'La date de début de l\'activité doit être supérieure à aujourd\'hui'
    )]
    #[Assert\GreaterThanOrEqual(
        propertyPath: 'dateLimiteInscription',
        message: 'La date de début de l\'activité ne doit pas être antérieure à la date limite d\'inscription'
    )]
    private ?\DateTimeInterface $dateHeureDebut = null;

    #[ORM\Column]
    #[Assert\NotNull(null,
        message: 'La durée ne peut pas être null'
    )]
    #[Assert\Positive(null,
        message: 'La durée ne peut pas être inférieure ou égale à zéro')]
    #[Assert\NotBlank(null,
        message: 'Le durée ne peut pas être vide'
    )]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(null,
        message: "La date limite d'inscription ne peut pas être nulle"
    )]
    #[Assert\NotBlank(
        message: "La date limite d'inscription ne peut pas être nulle"
    )]
    #[Assert\GreaterThan('today')]
    private ?\DateTimeInterface $dateLimiteInscription = null;

    #[ORM\Column]
    #[Assert\NotNull(null,
        message: "Le nombre maximum de participants ne peut pas être nul"
    )]
    #[Assert\NotBlank(null,
        message: "Le nombre maximum de participants ne peut pas être nul"
    )]
    #[Assert\Positive(null,
        message: 'Le nombre d\'inscrit maximum ne peut pas être inférieure ou égale à zéro')]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column(length: 500)]
    #[Assert\NotNull(null,
        message: "La description de l'évènement ne peut pas être nulle"
    )]
    #[Assert\NotBlank(null,
        message: "La description de l'évènement ne peut pas être nulle"
    )]
    private ?string $infosSortie = null;

    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'sorties')]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'sortiesOrganisees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organisateur = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): self
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): self
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(string $infosSortie): self
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }
}
