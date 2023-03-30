<?php

namespace App\modeles;

use App\Entity\Campus;


class Filter
{
    private ?string $keyWord = null;

    private ?Campus $campus = null ;

   private ?\DateTimeInterface $dateDebut = null;

    private ?\DateTimeInterface $dateFin = null;


    private ?bool $organisateurSorties = false;

    private ?bool $inscritSorties = false;

    private ?bool $nonInscritSorties = false;

    private ?bool $sortiesPassees =false;

    /**
     * @return string|null
     */
    public function getKeyWord(): ?string
    {
        return $this->keyWord;
    }

    /**
     * @param string|null $keyWord
     */
    public function setKeyWord(?string $keyWord): void
    {
        $this->keyWord = $keyWord;
    }

    /**
     * @return Campus|null
     */
    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    /**
     * @param Campus|null $campus
     */
    public function setCampus(?Campus $campus): void
    {
        $this->campus = $campus;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    /**
     * @param \DateTimeInterface|null $dateDebut
     */
    public function setDateDebut(?\DateTimeInterface $dateDebut): void
    {
        $this->dateDebut = $dateDebut;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    /**
     * @param \DateTimeInterface|null $dateFin
     */
    public function setDateFin(?\DateTimeInterface $dateFin): void
    {
        $this->dateFin = $dateFin;
    }

    /**
     * @return bool|null
     */
    public function getOrganisateurSorties(): ?bool
    {
        return $this->organisateurSorties;
    }

    /**
     * @param bool|null $organisateurSorties
     */
    public function setOrganisateurSorties(?bool $organisateurSorties): void
    {
        $this->organisateurSorties = $organisateurSorties;
    }



    /**
     * @return bool|null
     */
    public function getInscritSorties(): ?bool
    {
        return $this->inscritSorties;
    }

    /**
     * @param bool|null $inscritSorties
     */
    public function setInscritSorties(?bool $inscritSorties): void
    {
        $this->inscritSorties = $inscritSorties;
    }

    /**
     * @return bool|null
     */
    public function getNonInscritSorties(): ?bool
    {
        return $this->nonInscritSorties;
    }

    /**
     * @param bool|null $nonInscritSorties
     */
    public function setNonInscritSorties(?bool $nonInscritSorties): void
    {
        $this->nonInscritSorties = $nonInscritSorties;
    }

    /**
     * @return bool|null
     */
    public function getSortiesPassees(): ?bool
    {
        return $this->sortiesPassees;
    }

    /**
     * @param bool|null $sortiesPassees
     */
    public function setSortiesPassees(?bool $sortiesPassees): void
    {
        $this->sortiesPassees = $sortiesPassees;
    }


}